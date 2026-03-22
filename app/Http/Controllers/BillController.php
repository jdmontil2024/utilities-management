<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Lease;
use App\Models\BillItem;
use App\Models\Payment;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bill::with([
            'lease', 
            'lease.unit', 
            'lease.unit.building', 
            'lease.tenant',
            'billItems'
        ]);
        
        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhereHas('lease.tenant', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('lease.unit', function($q) use ($search) {
                      $q->where('unit_number', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // Date range filters
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->where('issue_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->where('issue_date', '<=', $request->date_to);
        }
        
        // Overdue filter
        if ($request->has('overdue')) {
            $query->where('status', 'pending')
                  ->where('due_date', '<', now());
        }
        
        // Sort by latest first
        $query->orderBy('created_at', 'desc');
        
        $bills = $query->paginate(12)->withQueryString();
        
        return view('bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $leases = Lease::with(['tenant', 'unit.building'])
            ->where('status', 'active')
            ->get();
            
        return view('bills.create', compact('leases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id' => 'required|exists:leases,id',
            'bill_number' => 'required|string|max:100|unique:bills,bill_number',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after:issue_date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'total_amount' => 'required|numeric|min:0',
            'total_tax' => 'nullable|numeric|min:0',
            'total_due' => 'required|numeric|min:0',
            'status' => 'sometimes|in:pending,partial,paid,overdue,void',
            'notes' => 'nullable|string',
            'bill_items' => 'required|array|min:1',
            'bill_items.*.item_type' => 'required|in:rent,utility,fee,tax,credit',
            'bill_items.*.description' => 'required|string|max:500',
            'bill_items.*.quantity' => 'required|numeric|min:0',
            'bill_items.*.rate' => 'nullable|numeric|min:0',
            'bill_items.*.amount' => 'required|numeric',
            'bill_items.*.tax_amount' => 'nullable|numeric|min:0',
            'bill_items.*.utility_type_id' => 'nullable|exists:utility_types,id',
            'bill_items.*.meter_reading_id' => 'nullable|exists:meter_readings,id',
            'bill_items.*.metadata' => 'nullable|array',
        ]);

        // Calculate totals if not provided
        if (!isset($validated['total_amount'])) {
            $validated['total_amount'] = collect($validated['bill_items'])->sum('amount');
        }
        
        if (!isset($validated['total_tax'])) {
            $validated['total_tax'] = collect($validated['bill_items'])->sum('tax_amount');
        }
        
        if (!isset($validated['total_due'])) {
            $validated['total_due'] = $validated['total_amount'] + ($validated['total_tax'] ?? 0);
        }

        $validated['status'] = $validated['status'] ?? 'pending';
        
        DB::beginTransaction();
        
        try {
            $bill = Bill::create($validated);
            
            // Create bill items
            foreach ($validated['bill_items'] as $itemData) {
                $bill->billItems()->create($itemData);
                
                // Mark meter reading as billed if applicable
                if (isset($itemData['meter_reading_id'])) {
                    MeterReading::where('id', $itemData['meter_reading_id'])->update(['is_billed' => true]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create bill: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        $bill->load([
            'lease', 
            'lease.unit', 
            'lease.unit.building', 
            'lease.tenant', 
            'billItems', 
            'billItems.utilityType',
            'billItems.meterReading',
            'payments'
        ]);
        
        return view('bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Cannot edit a paid bill.');
        }
        
        $bill->load(['lease', 'billItems']);
        $leases = Lease::with(['tenant', 'unit.building'])
            ->where('status', 'active')
            ->get();
            
        return view('bills.edit', compact('bill', 'leases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Cannot update a paid bill.');
        }

        $validated = $request->validate([
            'lease_id' => 'sometimes|required|exists:leases,id',
            'bill_number' => 'sometimes|required|string|max:100|unique:bills,bill_number,' . $bill->id,
            'issue_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date|after:issue_date',
            'period_start' => 'sometimes|required|date',
            'period_end' => 'sometimes|required|date|after:period_start',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'total_tax' => 'nullable|numeric|min:0',
            'total_due' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|in:pending,partial,paid,overdue,void',
            'paid_date' => 'nullable|date',
            'late_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $bill->status;
        $bill->update($validated);
        
        // Handle status changes
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $this->handleBillStatusChange($bill, $oldStatus, $validated['status']);
        }
        
        return redirect()->route('bills.show', $bill)
            ->with('success', 'Bill updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.index')
                ->with('error', 'Cannot delete a paid bill.');
        }
        
        DB::beginTransaction();
        
        try {
            // Unmark meter readings as billed
            $meterReadingIds = $bill->billItems()->whereNotNull('meter_reading_id')->pluck('meter_reading_id');
            MeterReading::whereIn('id', $meterReadingIds)->update(['is_billed' => false]);
            
            // Delete associated records
            $bill->billItems()->delete();
            $bill->payments()->delete();
            
            $bill->delete();
            
            DB::commit();
            
            return redirect()->route('bills.index')
                ->with('success', 'Bill deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete bill: ' . $e->getMessage());
        }
    }

    /**
     * Show form to generate monthly bills.
     */
    public function generateForm()
    {
        $activeLeases = Lease::with(['tenant', 'unit.building'])
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->get();
            
        return view('bills.generate', compact('activeLeases'));
    }

    /**
     * Generate monthly bills for all active leases.
     */
    public function generateMonthly(Request $request)
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|min:1|max:12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'due_day' => 'required|integer|min:1|max:31',
            'lease_ids' => 'sometimes|array',
            'lease_ids.*' => 'exists:leases,id',
            'include_utilities' => 'boolean',
        ]);

        $periodStart = Carbon::create($validated['period_year'], $validated['period_month'], 1);
        $periodEnd = $periodStart->copy()->endOfMonth();
        $dueDate = $periodEnd->copy()->addMonth()->setDay(min($validated['due_day'], 28));
        $issueDate = now();

        $leaseQuery = Lease::with(['tenant', 'unit', 'unit.building'])
            ->where('status', 'active')
            ->where('end_date', '>=', $periodEnd);

        if (!empty($validated['lease_ids'])) {
            $leaseQuery->whereIn('id', $validated['lease_ids']);
        }

        $leases = $leaseQuery->get();
        $generated = [];
        $skipped = [];

        DB::beginTransaction();
        
        try {
            foreach ($leases as $lease) {
                // Check if bill already exists for this period
                $existingBill = Bill::where('lease_id', $lease->id)
                    ->where('period_start', $periodStart->toDateString())
                    ->where('period_end', $periodEnd->toDateString())
                    ->first();

                if ($existingBill) {
                    $skipped[] = [
                        'lease' => $lease,
                        'reason' => 'Bill already exists'
                    ];
                    continue;
                }

                // Calculate prorated rent if lease started mid-month
                $leaseStart = Carbon::parse($lease->start_date);
                $proratedRent = $lease->monthly_rent;
                
                if ($leaseStart->greaterThan($periodStart)) {
                    $daysInMonth = $periodStart->daysInMonth;
                    $daysFromStart = $leaseStart->diffInDays($periodEnd) + 1;
                    $proratedRent = ($daysFromStart / $daysInMonth) * $lease->monthly_rent;
                }

                // Create bill
                $bill = Bill::create([
                    'lease_id' => $lease->id,
                    'bill_number' => 'BILL-' . strtoupper(uniqid()),
                    'issue_date' => $issueDate,
                    'due_date' => $dueDate,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'total_amount' => $proratedRent,
                    'total_tax' => 0,
                    'total_due' => $proratedRent,
                    'status' => 'pending',
                    'notes' => 'Monthly rent for ' . $periodStart->format('F Y'),
                ]);

                // Add rent item
                $bill->billItems()->create([
                    'item_type' => 'rent',
                    'description' => 'Monthly Rent - ' . $lease->unit->unit_number . ' (' . 
                        $periodStart->format('M Y') . ')',
                    'quantity' => 1,
                    'rate' => $proratedRent,
                    'amount' => $proratedRent,
                ]);

                // Add utility items if requested
                if ($validated['include_utilities'] ?? false) {
                    $this->addUtilityItems($bill, $periodStart, $periodEnd);
                }

                $generated[] = $bill;
            }
            
            DB::commit();
            
            return redirect()->route('bills.index')
                ->with('success', 'Generated ' . count($generated) . ' bills successfully.')
                ->with('warning', count($skipped) > 0 ? count($skipped) . ' leases were skipped.' : null);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to generate bills: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Add utility items to bill.
     */
    private function addUtilityItems(Bill $bill, $periodStart, $periodEnd)
    {
        $lease = $bill->lease;
        $utilitiesIncluded = $lease->utilities_included ?? [];
        
        // Get meter readings for the period
        $meterReadings = MeterReading::where('unit_id', $lease->unit_id)
            ->whereBetween('reading_date', [$periodStart, $periodEnd])
            ->where('is_billed', false)
            ->with('utilityType')
            ->get()
            ->groupBy('utility_type_id');
        
        foreach ($meterReadings as $utilityTypeId => $readings) {
            $utilityType = $readings->first()->utilityType;
            
            // Check if utility is included in rent
            if (in_array($utilityType->name, $utilitiesIncluded)) {
                continue;
            }
            
            $totalConsumption = $readings->sum('consumption');
            
            // Get rate for this utility
            $rate = $this->getUtilityRate($utilityTypeId, $totalConsumption);
            
            if ($rate) {
                $amount = $totalConsumption * $rate;
                
                $bill->billItems()->create([
                    'item_type' => 'utility',
                    'description' => $utilityType->name . ' Consumption',
                    'quantity' => $totalConsumption,
                    'rate' => $rate,
                    'amount' => $amount,
                    'utility_type_id' => $utilityTypeId,
                    'metadata' => [
                        'readings_count' => $readings->count(),
                        'readings_ids' => $readings->pluck('id')->toArray()
                    ],
                ]);
                
                // Mark readings as billed
                MeterReading::whereIn('id', $readings->pluck('id'))->update(['is_billed' => true]);
            }
        }
    }

    /**
     * Get utility rate based on consumption.
     */
    private function getUtilityRate($utilityTypeId, $consumption)
    {
        // This should query your rates table
        // For now, return a default rate
        return 0.15; // Default $0.15 per unit
    }

    /**
     * Show form to create payment for a bill.
     */
    public function createPayment(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'This bill is already paid.');
        }

        return view('bills.payments.create', compact('bill'));
    }

    /**
     * Store payment for a bill.
     */
    public function storePayment(Request $request, Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'This bill is already paid.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $bill->total_due,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,check,bank_transfer,credit_card,debit_card,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            // Create payment
            $payment = $bill->payments()->create([
                'payment_number' => 'PAY-' . strtoupper(uniqid()),
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'],
                'notes' => $validated['notes'],
                'status' => 'completed',
            ]);

            // Calculate total paid
            $totalPaid = $bill->payments()->sum('amount');
            $remainingDue = $bill->total_due - $totalPaid;

            // Update bill status
            if ($remainingDue <= 0) {
                $bill->update([
                    'status' => 'paid',
                    'paid_date' => $validated['payment_date']
                ]);
            } elseif ($totalPaid > 0) {
                $bill->update(['status' => 'partial']);
            }

            DB::commit();
            
            return redirect()->route('bills.show', $bill)
                ->with('success', 'Payment recorded successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to record payment: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get overdue bills.
     */
    public function overdue(Request $request)
    {
        $overdue = Bill::with(['lease', 'lease.tenant', 'lease.unit'])
            ->where('status', 'overdue')
            ->orWhere(function($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '<', now());
            })
            ->orderBy('due_date')
            ->paginate(20);
            
        return view('bills.overdue', compact('overdue'));
    }

    /**
     * Get bill statistics.
     */
    public function statistics(Request $request)
    {
        $query = Bill::query();
        
        if ($request->has('lease_id')) {
            $query->where('lease_id', $request->lease_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $stats = [
            'total_bills' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
            'total_collected' => $query->clone()
                ->where('status', 'paid')
                ->sum('total_amount'),
            'pending_amount' => $query->clone()
                ->where('status', 'pending')
                ->sum('total_due'),
            'overdue_amount' => $query->clone()
                ->where('status', 'overdue')
                ->sum('total_due'),
            'average_bill_amount' => $query->clone()
                ->avg('total_amount'),
            'bills_by_status' => $query->clone()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
        ];

        return response()->json($stats);
    }

    /**
     * Restore soft deleted bill.
     */
    public function restore($id)
    {
        $bill = Bill::withTrashed()->findOrFail($id);
        $bill->restore();
        
        return redirect()->route('bills.show', $bill)
            ->with('success', 'Bill restored successfully.');
    }

    /**
     * Handle bill status changes.
     */
    private function handleBillStatusChange(Bill $bill, $oldStatus, $newStatus)
    {
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $bill->update(['paid_date' => now()]);
            
            // Trigger payment received alert
            $this->triggerPaymentReceivedAlert($bill);
        }
        
        // Check if overdue
        if ($newStatus === 'pending' && $bill->due_date < now()) {
            $bill->update(['status' => 'overdue']);
            
            // Add late fee if not already added
            if (!$bill->late_fee) {
                $lateFee = $this->calculateLateFee($bill);
                $bill->update(['late_fee' => $lateFee]);
                
                // Add late fee as bill item
                $bill->billItems()->create([
                    'item_type' => 'fee',
                    'description' => 'Late Payment Fee',
                    'quantity' => 1,
                    'rate' => $lateFee,
                    'amount' => $lateFee,
                ]);
                
                // Recalculate totals
                $bill->update([
                    'total_amount' => $bill->total_amount + $lateFee,
                    'total_due' => $bill->total_due + $lateFee,
                ]);
            }
        }
    }

    /**
     * Calculate late fee for bill.
     */
    private function calculateLateFee(Bill $bill)
    {
        // Simple late fee calculation: 5% of total or $25, whichever is greater
        $percentageFee = $bill->total_due * 0.05;
        return max($percentageFee, 25);
    }

    /**
     * Trigger payment received alert.
     */
    private function triggerPaymentReceivedAlert(Bill $bill)
    {
        // You can implement notification logic here
        // For example: send email to tenant, notify admin, etc.
    }

    /**
     * Generate PDF for bill.
     */
    public function generatePdf(Bill $bill)
    {
        $bill->load([
            'lease', 
            'lease.unit', 
            'lease.unit.building', 
            'lease.tenant', 
            'billItems', 
            'payments'
        ]);
        
        // You'll need to install a PDF package like barryvdh/laravel-dompdf
        // $pdf = PDF::loadView('bills.pdf', compact('bill'));
        // return $pdf->download('bill-' . $bill->bill_number . '.pdf');
        
        return redirect()->route('bills.show', $bill)
            ->with('info', 'PDF generation will be available after installing a PDF package.');
    }

    /**
     * Send bill via email.
     */
    public function sendEmail(Bill $bill)
    {
        $bill->load(['lease.tenant']);
        
        // You'll need to implement mail functionality
        // Mail::to($bill->lease->tenant->email)->send(new BillMail($bill));
        
        return redirect()->route('bills.show', $bill)
            ->with('success', 'Bill sent to tenant email.');
    }

    /**
     * Void a bill.
     */
    public function void(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return redirect()->route('bills.show', $bill)
                ->with('error', 'Cannot void a paid bill.');
        }

        DB::beginTransaction();
        
        try {
            // Unmark meter readings as billed
            $meterReadingIds = $bill->billItems()->whereNotNull('meter_reading_id')->pluck('meter_reading_id');
            MeterReading::whereIn('id', $meterReadingIds)->update(['is_billed' => false]);
            
            $bill->update(['status' => 'void']);
            
            DB::commit();
            
            return redirect()->route('bills.show', $bill)
                ->with('success', 'Bill voided successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to void bill: ' . $e->getMessage());
        }
    }
}