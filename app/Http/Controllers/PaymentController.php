<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Bill;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['bill', 'bill.lease', 'bill.lease.tenant', 'paymentMethod']);
        
        if ($request->has('bill_id')) {
            $query->where('bill_id', $request->bill_id);
        }
        
        if ($request->has('lease_id')) {
            $query->whereHas('bill', function($q) use ($request) {
                $q->where('lease_id', $request->lease_id);
            });
        }
        
        if ($request->has('tenant_id')) {
            $query->whereHas('bill.lease', function($q) use ($request) {
                $q->where('tenant_id', $request->tenant_id);
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('bill.lease.tenant', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);
        return response()->json($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_number' => 'required|string|max:100|unique:payments,payment_number',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,check',
            'transaction_id' => 'nullable|string|max:200',
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'payment_details' => 'nullable|array',
        ]);

        $bill = Bill::find($validated['bill_id']);
        
        // Validate payment amount
        if ($validated['amount'] > $bill->total_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed bill total due'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validated['status'] = $validated['status'] ?? 'completed';
        
        DB::beginTransaction();
        
        try {
            $payment = Payment::create($validated);
            
            // Update bill status
            $this->updateBillStatus($bill, $validated['amount']);
            
            DB::commit();
            
            return response()->json($payment, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'bill', 
            'bill.lease', 
            'bill.lease.tenant', 
            'bill.lease.unit',
            'paymentMethod'
        ]);
        
        return response()->json($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'bill_id' => 'sometimes|required|exists:bills,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_number' => 'sometimes|required|string|max:100|unique:payments,payment_number,' . $payment->id,
            'amount' => 'sometimes|required|numeric|min:0.01',
            'payment_method' => 'sometimes|required|in:credit_card,debit_card,bank_transfer,cash,check',
            'transaction_id' => 'nullable|string|max:200',
            'status' => 'sometimes|in:pending,completed,failed,refunded',
            'payment_date' => 'sometimes|required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'payment_details' => 'nullable|array',
        ]);

        $oldStatus = $payment->status;
        $oldAmount = $payment->amount;
        
        DB::beginTransaction();
        
        try {
            $payment->update($validated);
            
            // Handle status changes
            if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
                $this->handlePaymentStatusChange($payment, $oldStatus, $validated['status']);
            }
            
            // Handle amount changes
            if (isset($validated['amount']) && $validated['amount'] != $oldAmount) {
                $this->updateBillStatus($payment->bill, $validated['amount'] - $oldAmount, true);
            }
            
            DB::commit();
            
            return response()->json($payment);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        if ($payment->status === 'completed') {
            return response()->json([
                'message' => 'Cannot delete a completed payment'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        DB::beginTransaction();
        
        try {
            // Update bill status before deleting payment
            $bill = $payment->bill;
            $totalPayments = $bill->payments()->where('status', 'completed')->sum('amount') - $payment->amount;
            $this->updateBillStatusBasedOnPayments($bill, $totalPayments);
            
            $payment->delete();
            
            DB::commit();
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore soft deleted payment.
     */
    public function restore($id)
    {
        $payment = Payment::withTrashed()->findOrFail($id);
        $payment->restore();
        return response()->json($payment);
    }

    /**
     * Record a cash payment.
     */
    public function recordCashPayment(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'received_by' => 'required|string|max:200',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $bill = Bill::find($validated['bill_id']);
        
        // Validate payment amount
        if ($validated['amount'] > $bill->total_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed bill total due'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        
        try {
            $payment = Payment::create([
                'bill_id' => $validated['bill_id'],
                'payment_number' => 'CASH-' . strtoupper(uniqid()),
                'amount' => $validated['amount'],
                'payment_method' => 'cash',
                'status' => 'completed',
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'payment_details' => [
                    'received_by' => $validated['received_by'],
                    'payment_type' => 'cash'
                ]
            ]);
            
            // Update bill status
            $this->updateBillStatus($bill, $validated['amount']);
            
            DB::commit();
            
            return response()->json($payment, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record cash payment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Record a check payment.
     */
    public function recordCheckPayment(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'check_number' => 'required|string|max:100',
            'bank_name' => 'required|string|max:200',
            'account_holder' => 'required|string|max:200',
            'notes' => 'nullable|string',
        ]);

        $bill = Bill::find($validated['bill_id']);
        
        // Validate payment amount
        if ($validated['amount'] > $bill->total_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed bill total due'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        
        try {
            $payment = Payment::create([
                'bill_id' => $validated['bill_id'],
                'payment_number' => 'CHECK-' . strtoupper(uniqid()),
                'amount' => $validated['amount'],
                'payment_method' => 'check',
                'status' => 'completed',
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['check_number'],
                'notes' => $validated['notes'],
                'payment_details' => [
                    'check_number' => $validated['check_number'],
                    'bank_name' => $validated['bank_name'],
                    'account_holder' => $validated['account_holder'],
                    'payment_type' => 'check'
                ]
            ]);
            
            // Update bill status
            $this->updateBillStatus($bill, $validated['amount']);
            
            DB::commit();
            
            return response()->json($payment, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record check payment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Process a refund.
     */
    public function refund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'refund_date' => 'required|date',
            'refund_reason' => 'required|string',
            'refund_method' => 'required|in:original,check,cash,bank_transfer',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($payment->status !== 'completed') {
            return response()->json([
                'message' => 'Only completed payments can be refunded'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        
        try {
            // Create refund payment (negative amount)
            $refundPayment = Payment::create([
                'bill_id' => $payment->bill_id,
                'payment_method_id' => $payment->payment_method_id,
                'payment_number' => 'REFUND-' . strtoupper(uniqid()),
                'amount' => -$validated['refund_amount'],
                'payment_method' => $validated['refund_method'],
                'status' => 'refunded',
                'payment_date' => $validated['refund_date'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'payment_details' => [
                    'original_payment_id' => $payment->id,
                    'refund_reason' => $validated['refund_reason'],
                    'payment_type' => 'refund'
                ]
            ]);
            
            // Update original payment if full refund
            if ($validated['refund_amount'] == $payment->amount) {
                $payment->update(['status' => 'refunded']);
            }
            
            // Update bill status
            $this->updateBillStatus($payment->bill, -$validated['refund_amount']);
            
            DB::commit();
            
            return response()->json($refundPayment, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process refund',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update bill status based on payments.
     */
    private function updateBillStatus(Bill $bill, $paymentAmount, $isUpdate = false)
    {
        $totalPayments = $bill->payments()
            ->where('status', 'completed')
            ->sum('amount');
            
        if ($isUpdate) {
            $totalPayments += $paymentAmount;
        }
        
        $this->updateBillStatusBasedOnPayments($bill, $totalPayments);
    }

    /**
     * Update bill status based on total payments.
     */
    private function updateBillStatusBasedOnPayments(Bill $bill, $totalPayments)
    {
        if ($totalPayments <= 0) {
            $status = 'pending';
        } elseif ($totalPayments >= $bill->total_due) {
            $status = 'paid';
            $bill->update(['paid_date' => now()]);
        } else {
            $status = 'partial';
        }
        
        // Check if overdue
        if (($status === 'pending' || $status === 'partial') && $bill->due_date < now()) {
            $status = 'overdue';
        }
        
        $bill->update(['status' => $status]);
    }

    /**
     * Handle payment status changes.
     */
    private function handlePaymentStatusChange(Payment $payment, $oldStatus, $newStatus)
    {
        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            // Payment was completed but now is not (failed, pending, etc.)
            $this->updateBillStatus($payment->bill, -$payment->amount, true);
        } elseif ($oldStatus !== 'completed' && $newStatus === 'completed') {
            // Payment is now completed
            $this->updateBillStatus($payment->bill, $payment->amount, true);
        }
    }

    /**
     * Get payment statistics.
     */
    public function statistics(Request $request)
    {
        $query = Payment::where('status', 'completed');
        
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $stats = [
            'total_payments' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'average_payment' => $query->avg('amount'),
            'payments_by_method' => $query->clone()
                ->select('payment_method', \DB::raw('count(*) as count, sum(amount) as total'))
                ->groupBy('payment_method')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->payment_method => [
                        'count' => $item->count,
                        'total' => $item->total
                    ]];
                }),
            'daily_collections' => $query->clone()
                ->select(\DB::raw('DATE(payment_date) as date'), \DB::raw('count(*) as count, sum(amount) as total'))
                ->groupBy(\DB::raw('DATE(payment_date)'))
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Process bulk payments.
     */
    public function bulkProcess(Request $request)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.bill_id' => 'required|exists:bills,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
            'payments.*.payment_method' => 'required|in:credit_card,debit_card,bank_transfer,cash,check',
            'payments.*.payment_date' => 'required|date',
            'payments.*.reference_number' => 'nullable|string|max:100',
            'payments.*.notes' => 'nullable|string',
        ]);

        $processed = [];
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($validated['payments'] as $index => $paymentData) {
                try {
                    $bill = Bill::find($paymentData['bill_id']);
                    
                    // Validate payment amount
                    if ($paymentData['amount'] > $bill->total_due) {
                        throw new \Exception('Payment amount cannot exceed bill total due');
                    }
                    
                    $payment = Payment::create([
                        'bill_id' => $paymentData['bill_id'],
                        'payment_number' => 'BULK-' . strtoupper(uniqid()),
                        'amount' => $paymentData['amount'],
                        'payment_method' => $paymentData['payment_method'],
                        'status' => 'completed',
                        'payment_date' => $paymentData['payment_date'],
                        'reference_number' => $paymentData['reference_number'] ?? null,
                        'notes' => $paymentData['notes'] ?? null,
                    ]);
                    
                    // Update bill status
                    $this->updateBillStatus($bill, $paymentData['amount']);
                    
                    $processed[] = $payment;
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'data' => $paymentData
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bulk payment processing completed',
                'processed' => count($processed),
                'errors' => $errors,
                'data' => $processed
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bulk payment processing failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}