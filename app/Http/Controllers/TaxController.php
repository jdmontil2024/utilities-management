<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\TaxJurisdiction;
use App\Models\Bill;
use App\Models\BillItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    /**
     * Display a listing of taxes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Tax::with(['taxJurisdiction', 'bill', 'billItem']);
            
            // Filter by jurisdiction
            if ($request->has('tax_jurisdiction_id')) {
                $query->where('tax_jurisdiction_id', $request->tax_jurisdiction_id);
            }
            
            // Filter by bill
            if ($request->has('bill_id')) {
                $query->where('bill_id', $request->bill_id);
            }
            
            // Filter by bill item
            if ($request->has('bill_item_id')) {
                $query->where('bill_item_id', $request->bill_item_id);
            }
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $taxes = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $taxes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch taxes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tax.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_jurisdiction_id' => 'required|exists:tax_jurisdictions,id',
            'bill_id' => 'nullable|exists:bills,id',
            'bill_item_id' => 'nullable|exists:bill_items,id',
            'taxable_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'is_inclusive' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validate that either bill_id or bill_item_id is provided
            if (!$request->has('bill_id') && !$request->has('bill_item_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either bill_id or bill_item_id must be provided'
                ], 422);
            }
            
            // Validate that not both bill_id and bill_item_id are provided
            if ($request->has('bill_id') && $request->has('bill_item_id')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only one of bill_id or bill_item_id can be provided'
                ], 422);
            }
            
            $tax = Tax::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Tax created successfully',
                'data' => $tax->load(['taxJurisdiction', 'bill', 'billItem'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tax.
     */
    public function show(Tax $tax): JsonResponse
    {
        try {
            $tax->load(['taxJurisdiction', 'bill', 'billItem']);
            return response()->json([
                'success' => true,
                'data' => $tax
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified tax.
     */
    public function update(Request $request, Tax $tax): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tax_jurisdiction_id' => 'sometimes|exists:tax_jurisdictions,id',
            'taxable_amount' => 'sometimes|numeric|min:0',
            'tax_amount' => 'sometimes|numeric|min:0',
            'is_inclusive' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tax->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Tax updated successfully',
                'data' => $tax->load(['taxJurisdiction', 'bill', 'billItem'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tax.
     */
    public function destroy(Tax $tax): JsonResponse
    {
        try {
            $tax->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate tax for a bill.
     */
    public function calculateForBill(Bill $bill): JsonResponse
    {
        try {
            // Get all active tax jurisdictions
            $jurisdictions = TaxJurisdiction::where('is_active', true)
                ->where('effective_date', '<=', now())
                ->where(function($query) {
                    $query->where('expiration_date', '>=', now())
                          ->orWhereNull('expiration_date');
                })
                ->get();
            
            $taxDetails = [];
            $totalTax = 0;
            
            // Calculate tax for bill items
            foreach ($bill->billItems as $item) {
                $itemTaxableAmount = $item->amount;
                
                foreach ($jurisdictions as $jurisdiction) {
                    $taxAmount = $itemTaxableAmount * $jurisdiction->rate;
                    $totalTax += $taxAmount;
                    
                    // Create or update tax record
                    Tax::updateOrCreate(
                        [
                            'tax_jurisdiction_id' => $jurisdiction->id,
                            'bill_item_id' => $item->id
                        ],
                        [
                            'taxable_amount' => $itemTaxableAmount,
                            'tax_amount' => $taxAmount,
                            'is_inclusive' => false
                        ]
                    );
                    
                    $taxDetails[] = [
                        'bill_item_id' => $item->id,
                        'item_description' => $item->description,
                        'jurisdiction_name' => $jurisdiction->name,
                        'jurisdiction_type' => $jurisdiction->type,
                        'rate' => $jurisdiction->rate,
                        'taxable_amount' => $itemTaxableAmount,
                        'tax_amount' => $taxAmount
                    ];
                }
            }
            
            // Update bill with total tax
            $bill->update([
                'total_tax' => $totalTax,
                'total_due' => $bill->total_amount + $totalTax
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tax calculated successfully',
                'data' => [
                    'bill_id' => $bill->id,
                    'bill_number' => $bill->bill_number,
                    'total_amount' => $bill->total_amount,
                    'total_tax' => $totalTax,
                    'total_due' => $bill->total_amount + $totalTax,
                    'tax_details' => $taxDetails
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate tax for bill',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get taxes by jurisdiction.
     */
    public function byJurisdiction($jurisdictionId): JsonResponse
    {
        try {
            $taxes = Tax::where('tax_jurisdiction_id', $jurisdictionId)
                ->with(['bill', 'billItem'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $taxes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch taxes by jurisdiction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax summary.
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $query = Tax::query();
            
            // Filter by date range
            if ($request->has('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Group by jurisdiction
            $jurisdictionSummary = $query->selectRaw('tax_jurisdiction_id, 
                                                     COUNT(*) as transaction_count,
                                                     SUM(taxable_amount) as total_taxable,
                                                     SUM(tax_amount) as total_tax')
                ->groupBy('tax_jurisdiction_id')
                ->with('taxJurisdiction')
                ->get();
            
            // Total summary
            $totalSummary = $query->selectRaw('COUNT(*) as total_transactions,
                                               SUM(taxable_amount) as grand_total_taxable,
                                               SUM(tax_amount) as grand_total_tax')
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'by_jurisdiction' => $jurisdictionSummary,
                    'totals' => $totalSummary
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tax summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}