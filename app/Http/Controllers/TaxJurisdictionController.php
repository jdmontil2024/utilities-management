<?php

namespace App\Http\Controllers;

use App\Models\TaxJurisdiction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaxJurisdictionController extends Controller
{
    /**
     * Display a listing of tax jurisdictions.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TaxJurisdiction::query();
            
            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
            
            // Filter by effective date
            if ($request->has('effective_date')) {
                $query->where('effective_date', '<=', $request->effective_date)
                      ->where(function($q) use ($request) {
                          $q->where('expiration_date', '>=', $request->effective_date)
                            ->orWhereNull('expiration_date');
                      });
            }
            
            // Sort
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
            
            $jurisdictions = $query->paginate($request->get('per_page', 20));
            
            return response()->json([
                'success' => true,
                'data' => $jurisdictions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax jurisdictions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created tax jurisdiction.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:city,county,state,federal',
            'jurisdiction_code' => 'nullable|string|max:100',
            'rate' => 'required|numeric|between:0,1',
            'is_active' => 'boolean',
            'effective_date' => 'required|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jurisdiction = TaxJurisdiction::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Tax jurisdiction created successfully',
                'data' => $jurisdiction
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tax jurisdiction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified tax jurisdiction.
     */
    public function show(TaxJurisdiction $taxJurisdiction): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $taxJurisdiction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tax jurisdiction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified tax jurisdiction.
     */
    public function update(Request $request, TaxJurisdiction $taxJurisdiction): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:city,county,state,federal',
            'jurisdiction_code' => 'nullable|string|max:100',
            'rate' => 'sometimes|numeric|between:0,1',
            'is_active' => 'sometimes|boolean',
            'effective_date' => 'sometimes|date',
            'expiration_date' => 'nullable|date|after:effective_date',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $taxJurisdiction->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Tax jurisdiction updated successfully',
                'data' => $taxJurisdiction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax jurisdiction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified tax jurisdiction.
     */
    public function destroy(TaxJurisdiction $taxJurisdiction): JsonResponse
    {
        try {
            // Check if jurisdiction has taxes
            if ($taxJurisdiction->taxes()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete tax jurisdiction with associated taxes. Delete taxes first.'
                ], 400);
            }
            
            $taxJurisdiction->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax jurisdiction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete tax jurisdiction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(TaxJurisdiction $taxJurisdiction): JsonResponse
    {
        try {
            $taxJurisdiction->update([
                'is_active' => !$taxJurisdiction->is_active
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tax jurisdiction status updated',
                'data' => $taxJurisdiction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tax jurisdiction status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active tax jurisdictions.
     */
    public function active(): JsonResponse
    {
        try {
            $jurisdictions = TaxJurisdiction::where('is_active', true)
                ->where('effective_date', '<=', now())
                ->where(function($query) {
                    $query->where('expiration_date', '>=', now())
                          ->orWhereNull('expiration_date');
                })
                ->orderBy('type')
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $jurisdictions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active tax jurisdictions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate tax for an amount.
     */
    public function calculateTax(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'jurisdiction_ids' => 'nullable|array',
            'jurisdiction_ids.*' => 'exists:tax_jurisdictions,id',
            'date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $amount = $request->amount;
            $date = $request->date ?: now();
            $jurisdictionIds = $request->jurisdiction_ids;
            
            // Get applicable jurisdictions
            $query = TaxJurisdiction::where('is_active', true)
                ->where('effective_date', '<=', $date)
                ->where(function($q) use ($date) {
                    $q->where('expiration_date', '>=', $date)
                      ->orWhereNull('expiration_date');
                });
            
            if ($jurisdictionIds) {
                $query->whereIn('id', $jurisdictionIds);
            }
            
            $jurisdictions = $query->get();
            
            $taxDetails = [];
            $totalTax = 0;
            
            foreach ($jurisdictions as $jurisdiction) {
                $taxAmount = $amount * $jurisdiction->rate;
                $totalTax += $taxAmount;
                
                $taxDetails[] = [
                    'jurisdiction_id' => $jurisdiction->id,
                    'name' => $jurisdiction->name,
                    'type' => $jurisdiction->type,
                    'rate' => $jurisdiction->rate,
                    'rate_percentage' => $jurisdiction->rate * 100,
                    'tax_amount' => $taxAmount
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'taxable_amount' => $amount,
                    'total_tax' => $totalTax,
                    'total_with_tax' => $amount + $totalTax,
                    'effective_tax_rate' => $amount > 0 ? ($totalTax / $amount) * 100 : 0,
                    'tax_details' => $taxDetails
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate tax',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax summary by type.
     */
    public function summary(): JsonResponse
    {
        try {
            $summary = TaxJurisdiction::selectRaw('type, COUNT(*) as count, AVG(rate) as avg_rate, 
                                                  SUM(CASE WHEN is_active THEN 1 ELSE 0 END) as active_count')
                ->groupBy('type')
                ->get();
            
            $totalActive = TaxJurisdiction::where('is_active', true)->count();
            $totalExpired = TaxJurisdiction::where('expiration_date', '<', now())->count();
            $totalFuture = TaxJurisdiction::where('effective_date', '>', now())->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'by_type' => $summary,
                    'totals' => [
                        'active' => $totalActive,
                        'expired' => $totalExpired,
                        'future' => $totalFuture,
                        'total' => TaxJurisdiction::count()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get tax jurisdiction summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}