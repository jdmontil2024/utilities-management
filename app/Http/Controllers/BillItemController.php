<?php

namespace App\Http\Controllers;

use App\Models\BillItem;
use App\Models\Bill;
use App\Models\UtilityType;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BillItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BillItem::with(['bill', 'utilityType', 'meterReading']);
        
        if ($request->has('bill_id')) {
            $query->where('bill_id', $request->bill_id);
        }
        
        if ($request->has('item_type')) {
            $query->where('item_type', $request->item_type);
        }
        
        if ($request->has('utility_type_id')) {
            $query->where('utility_type_id', $request->utility_type_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereHas('bill', function($q) use ($request) {
                $q->where('issue_date', '>=', $request->date_from);
            });
        }
        
        if ($request->has('date_to')) {
            $query->whereHas('bill', function($q) use ($request) {
                $q->where('issue_date', '<=', $request->date_to);
            });
        }
        
        $billItems = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($billItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'item_type' => 'required|in:rent,utility,fee,tax,credit',
            'description' => 'required|string|max:500',
            'quantity' => 'required|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'amount' => 'required|numeric',
            'tax_amount' => 'nullable|numeric|min:0',
            'utility_type_id' => 'nullable|exists:utility_types,id',
            'meter_reading_id' => 'nullable|exists:meter_readings,id',
            'metadata' => 'nullable|array',
        ]);

        // Validate item type specific rules
        if ($validated['item_type'] === 'utility' && !isset($validated['utility_type_id'])) {
            return response()->json([
                'message' => 'Utility items require a utility_type_id'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Validate that meter reading belongs to the same unit as the bill
        if (isset($validated['meter_reading_id'])) {
            $meterReading = MeterReading::find($validated['meter_reading_id']);
            $bill = Bill::find($validated['bill_id']);
            
            if ($meterReading->unit_id !== $bill->lease->unit_id) {
                return response()->json([
                    'message' => 'Meter reading does not belong to the same unit as the bill'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Calculate amount if rate and quantity provided
        if (isset($validated['rate']) && isset($validated['quantity'])) {
            $calculatedAmount = $validated['rate'] * $validated['quantity'];
            if (abs($calculatedAmount - $validated['amount']) > 0.01) {
                return response()->json([
                    'message' => 'Amount does not match rate × quantity',
                    'calculated_amount' => $calculatedAmount,
                    'provided_amount' => $validated['amount']
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        DB::beginTransaction();
        
        try {
            $billItem = BillItem::create($validated);
            
            // Mark meter reading as billed if applicable
            if (isset($validated['meter_reading_id'])) {
                MeterReading::where('id', $validated['meter_reading_id'])->update(['is_billed' => true]);
            }
            
            // Update bill totals
            $this->updateBillTotals($billItem->bill);
            
            DB::commit();
            
            return response()->json($billItem, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create bill item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BillItem $billItem)
    {
        $billItem->load(['bill', 'bill.lease', 'utilityType', 'meterReading']);
        return response()->json($billItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BillItem $billItem)
    {
        $validated = $request->validate([
            'bill_id' => 'sometimes|required|exists:bills,id',
            'item_type' => 'sometimes|required|in:rent,utility,fee,tax,credit',
            'description' => 'sometimes|required|string|max:500',
            'quantity' => 'sometimes|required|numeric|min:0',
            'rate' => 'nullable|numeric|min:0',
            'amount' => 'sometimes|required|numeric',
            'tax_amount' => 'nullable|numeric|min:0',
            'utility_type_id' => 'nullable|exists:utility_types,id',
            'meter_reading_id' => 'nullable|exists:meter_readings,id',
            'metadata' => 'nullable|array',
        ]);

        // Validate item type specific rules
        if (isset($validated['item_type']) && $validated['item_type'] === 'utility' && !isset($validated['utility_type_id'])) {
            return response()->json([
                'message' => 'Utility items require a utility_type_id'
            ], Response::HTTP_BAD_REQUEST);
        }

        $oldMeterReadingId = $billItem->meter_reading_id;
        $oldAmount = $billItem->amount;
        $oldTaxAmount = $billItem->tax_amount;
        
        DB::beginTransaction();
        
        try {
            $billItem->update($validated);
            
            // Handle meter reading changes
            if (isset($validated['meter_reading_id']) && $validated['meter_reading_id'] !== $oldMeterReadingId) {
                // Unmark old meter reading
                if ($oldMeterReadingId) {
                    MeterReading::where('id', $oldMeterReadingId)->update(['is_billed' => false]);
                }
                
                // Mark new meter reading
                MeterReading::where('id', $validated['meter_reading_id'])->update(['is_billed' => true]);
            }
            
            // Update bill totals if amount changed
            if (isset($validated['amount']) || isset($validated['tax_amount'])) {
                $this->updateBillTotals($billItem->bill);
            }
            
            DB::commit();
            
            return response()->json($billItem);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update bill item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BillItem $billItem)
    {
        DB::beginTransaction();
        
        try {
            $bill = $billItem->bill;
            
            // Unmark meter reading as billed if applicable
            if ($billItem->meter_reading_id) {
                MeterReading::where('id', $billItem->meter_reading_id)->update(['is_billed' => false]);
            }
            
            $billItem->delete();
            
            // Update bill totals
            $this->updateBillTotals($bill);
            
            DB::commit();
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete bill item',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update bill totals.
     */
    private function updateBillTotals(Bill $bill)
    {
        $totalAmount = $bill->billItems()->sum('amount');
        $totalTax = $bill->billItems()->sum('tax_amount');
        
        $bill->update([
            'total_amount' => $totalAmount,
            'total_tax' => $totalTax,
            'total_due' => $totalAmount + $totalTax,
        ]);
    }

    /**
     * Get bill item statistics.
     */
    public function statistics(Request $request)
    {
        $query = BillItem::query();
        
        if ($request->has('bill_id')) {
            $query->where('bill_id', $request->bill_id);
        }
        
        if ($request->has('date_from')) {
            $query->whereHas('bill', function($q) use ($request) {
                $q->where('issue_date', '>=', $request->date_from);
            });
        }
        
        if ($request->has('date_to')) {
            $query->whereHas('bill', function($q) use ($request) {
                $q->where('issue_date', '<=', $request->date_to);
            });
        }

        $stats = [
            'total_items' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_tax' => $query->sum('tax_amount'),
            'items_by_type' => $query->clone()
                ->select('item_type', DB::raw('count(*) as count, sum(amount) as total_amount'))
                ->groupBy('item_type')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->item_type => [
                        'count' => $item->count,
                        'total_amount' => $item->total_amount
                    ]];
                }),
            'utility_breakdown' => $query->clone()
                ->where('item_type', 'utility')
                ->with('utilityType')
                ->select('utility_type_id', DB::raw('count(*) as count, sum(amount) as total_amount, sum(quantity) as total_quantity'))
                ->groupBy('utility_type_id')
                ->get()
                ->map(function($item) {
                    return [
                        'utility_type' => $item->utilityType->name ?? 'Unknown',
                        'count' => $item->count,
                        'total_amount' => $item->total_amount,
                        'total_quantity' => $item->total_quantity,
                        'average_rate' => $item->total_quantity > 0 ? $item->total_amount / $item->total_quantity : 0,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Get average utility rates.
     */
    public function averageRates(Request $request)
    {
        $validated = $request->validate([
            'utility_type_id' => 'required|exists:utility_types,id',
            'period_months' => 'nullable|integer|min:1|max:24',
        ]);

        $periodMonths = $validated['period_months'] ?? 12;
        
        $rates = BillItem::where('utility_type_id', $validated['utility_type_id'])
            ->where('item_type', 'utility')
            ->where('rate', '>', 0)
            ->whereHas('bill', function($q) use ($periodMonths) {
                $q->where('issue_date', '>=', now()->subMonths($periodMonths));
            })
            ->select([
                DB::raw('AVG(rate) as average_rate'),
                DB::raw('MIN(rate) as min_rate'),
                DB::raw('MAX(rate) as max_rate'),
                DB::raw('COUNT(*) as samples'),
                DB::raw('DATE_FORMAT(bills.issue_date, "%Y-%m") as month'),
            ])
            ->join('bills', 'bill_items.bill_id', '=', 'bills.id')
            ->groupBy(DB::raw('DATE_FORMAT(bills.issue_date, "%Y-%m")'))
            ->orderBy('month')
            ->get();

        $utilityType = UtilityType::find($validated['utility_type_id']);

        return response()->json([
            'utility_type' => $utilityType->name,
            'unit' => $utilityType->unit,
            'period_months' => $periodMonths,
            'overall_average' => $rates->avg('average_rate'),
            'monthly_rates' => $rates,
            'trend' => $this->calculateRateTrend($rates->pluck('average_rate')->toArray()),
        ]);
    }

    /**
     * Calculate rate trend.
     */
    private function calculateRateTrend($rates)
    {
        if (count($rates) < 2) {
            return 'insufficient_data';
        }

        $first = $rates[0];
        $last = $rates[count($rates) - 1];
        $change = $last - $first;
        $changePercent = $first > 0 ? ($change / $first) * 100 : 0;

        if ($changePercent > 5) {
            return 'increasing';
        } elseif ($changePercent < -5) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }

    /**
     * Apply bulk adjustments to bill items.
     */
    public function bulkAdjust(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'adjustments' => 'required|array|min:1',
            'adjustments.*.id' => 'required|exists:bill_items,id',
            'adjustments.*.action' => 'required|in:update_amount,update_quantity,update_rate,remove',
            'adjustments.*.value' => 'required_unless:adjustments.*.action,remove|numeric',
            'adjustments.*.notes' => 'nullable|string',
        ]);

        $bill = Bill::find($validated['bill_id']);
        $adjusted = [];
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($validated['adjustments'] as $adjustment) {
                try {
                    $billItem = BillItem::find($adjustment['id']);
                    
                    if ($billItem->bill_id != $bill->id) {
                        throw new \Exception('Bill item does not belong to the specified bill');
                    }

                    switch ($adjustment['action']) {
                        case 'update_amount':
                            $billItem->update(['amount' => $adjustment['value']]);
                            break;
                            
                        case 'update_quantity':
                            $billItem->update(['quantity' => $adjustment['value']]);
                            // Recalculate amount if rate exists
                            if ($billItem->rate) {
                                $billItem->update(['amount' => $billItem->rate * $adjustment['value']]);
                            }
                            break;
                            
                        case 'update_rate':
                            $billItem->update([
                                'rate' => $adjustment['value'],
                                'amount' => $billItem->quantity * $adjustment['value']
                            ]);
                            break;
                            
                        case 'remove':
                            // Unmark meter reading if applicable
                            if ($billItem->meter_reading_id) {
                                MeterReading::where('id', $billItem->meter_reading_id)->update(['is_billed' => false]);
                            }
                            $billItem->delete();
                            break;
                    }
                    
                    $adjusted[] = [
                        'id' => $billItem->id,
                        'action' => $adjustment['action'],
                        'success' => true,
                    ];
                    
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $adjustment['id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }
            
            // Update bill totals
            $this->updateBillTotals($bill);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Bulk adjustment completed',
                'adjusted' => count($adjusted),
                'errors' => $errors,
                'results' => $adjusted,
                'updated_bill_totals' => [
                    'total_amount' => $bill->total_amount,
                    'total_tax' => $bill->total_tax,
                    'total_due' => $bill->total_due,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Bulk adjustment failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add credit to bill.
     */
    public function addCredit(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
            'approved_by' => 'required|string|max:200',
            'reference_number' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();
        
        try {
            $billItem = BillItem::create([
                'bill_id' => $validated['bill_id'],
                'item_type' => 'credit',
                'description' => $validated['description'],
                'quantity' => 1,
                'rate' => -$validated['amount'], // Negative rate for credit
                'amount' => -$validated['amount'], // Negative amount for credit
                'metadata' => array_merge($validated['metadata'] ?? [], [
                    'reason' => $validated['reason'],
                    'approved_by' => $validated['approved_by'],
                    'reference_number' => $validated['reference_number'],
                    'applied_date' => now()->toDateString(),
                ]),
            ]);
            
            // Update bill totals
            $this->updateBillTotals($billItem->bill);
            
            DB::commit();
            
            return response()->json($billItem, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add credit',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add fee to bill.
     */
    public function addFee(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0.01',
            'fee_type' => 'required|in:late,nsf,processing,damage,other',
            'reason' => 'required|string',
            'approved_by' => 'required|string|max:200',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();
        
        try {
            $billItem = BillItem::create([
                'bill_id' => $validated['bill_id'],
                'item_type' => 'fee',
                'description' => $validated['description'],
                'quantity' => 1,
                'rate' => $validated['amount'],
                'amount' => $validated['amount'],
                'metadata' => array_merge($validated['metadata'] ?? [], [
                    'fee_type' => $validated['fee_type'],
                    'reason' => $validated['reason'],
                    'approved_by' => $validated['approved_by'],
                    'applied_date' => now()->toDateString(),
                ]),
            ]);
            
            // Update bill totals
            $this->updateBillTotals($billItem->bill);
            
            DB::commit();
            
            return response()->json($billItem, Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to add fee',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}