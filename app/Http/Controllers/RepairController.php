<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\MaintenanceRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Repair::with([
            'maintenanceRequest', 
            'maintenanceRequest.unit', 
            'maintenanceRequest.tenant',
            'maintenanceRequest.maintenanceCategory',
            'vendor'
        ]);
        
        if ($request->has('maintenance_request_id')) {
            $query->where('maintenance_request_id', $request->maintenance_request_id);
        }
        
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        if ($request->has('repair_type')) {
            $query->where('repair_type', $request->repair_type);
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->has('warranty_active')) {
            if ($request->boolean('warranty_active')) {
                $query->whereNotNull('warranty_expires')
                      ->where('warranty_expires', '>=', now());
            } else {
                $query->where(function($q) {
                    $q->whereNull('warranty_expires')
                      ->orWhere('warranty_expires', '<', now());
                });
            }
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('work_performed', 'like', "%{$search}%")
                  ->orWhere('parts_used', 'like', "%{$search}%")
                  ->orWhereHas('maintenanceRequest', function($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhereHas('unit', function($q) use ($search) {
                            $q->where('unit_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tenant', function($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                  })
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $repairs = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($repairs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'repair_type' => 'required|in:minor,major,emergency,replacement',
            'diagnosis' => 'required|string',
            'work_performed' => 'required|string',
            'parts_used' => 'nullable|string',
            'labor_hours' => 'nullable|numeric|min:0',
            'labor_rate' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
            'warranty_period' => 'nullable|string|max:100',
            'warranty_expires' => 'nullable|date|after:today',
            'before_photos' => 'nullable|array',
            'before_photos.*' => 'url',
            'after_photos' => 'nullable|array',
            'after_photos.*' => 'url',
        ]);

        // Calculate total cost if not provided
        if (!isset($validated['total_cost'])) {
            $laborCost = ($validated['labor_hours'] ?? 0) * ($validated['labor_rate'] ?? 0);
            $partsCost = $validated['parts_cost'] ?? 0;
            $validated['total_cost'] = $laborCost + $partsCost;
        }

        // Calculate warranty expiration if period provided
        if (isset($validated['warranty_period']) && !isset($validated['warranty_expires'])) {
            $validated['warranty_expires'] = $this->calculateWarrantyExpiration($validated['warranty_period']);
        }

        DB::beginTransaction();
        
        try {
            $repair = Repair::create($validated);
            
            // Update maintenance request with actual cost
            $maintenanceRequest = $repair->maintenanceRequest;
            $maintenanceRequest->update([
                'actual_cost' => $validated['total_cost'],
                'status' => 'completed',
                'completion_date' => now(),
                'resolution_notes' => $repair->work_performed
            ]);
            
            DB::commit();
            
            return response()->json($repair->load(['maintenanceRequest', 'vendor']), Response::HTTP_CREATED);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create repair record',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Repair $repair)
    {
        $repair->load([
            'maintenanceRequest', 
            'maintenanceRequest.unit', 
            'maintenanceRequest.tenant',
            'maintenanceRequest.maintenanceCategory',
            'maintenanceRequest.assignedVendor',
            'maintenanceRequest.assignedStaff',
            'vendor'
        ]);
        
        return response()->json($repair);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'maintenance_request_id' => 'sometimes|required|exists:maintenance_requests,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'repair_type' => 'sometimes|required|in:minor,major,emergency,replacement',
            'diagnosis' => 'sometimes|required|string',
            'work_performed' => 'sometimes|required|string',
            'parts_used' => 'nullable|string',
            'labor_hours' => 'nullable|numeric|min:0',
            'labor_rate' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'total_cost' => 'sometimes|required|numeric|min:0',
            'warranty_period' => 'nullable|string|max:100',
            'warranty_expires' => 'nullable|date',
            'before_photos' => 'nullable|array',
            'before_photos.*' => 'url',
            'after_photos' => 'nullable|array',
            'after_photos.*' => 'url',
        ]);

        // Recalculate total cost if labor or parts changed
        if (isset($validated['labor_hours']) || isset($validated['labor_rate']) || isset($validated['parts_cost'])) {
            $laborHours = $validated['labor_hours'] ?? $repair->labor_hours ?? 0;
            $laborRate = $validated['labor_rate'] ?? $repair->labor_rate ?? 0;
            $partsCost = $validated['parts_cost'] ?? $repair->parts_cost ?? 0;
            
            if (!isset($validated['total_cost'])) {
                $validated['total_cost'] = ($laborHours * $laborRate) + $partsCost;
            }
        }

        // Recalculate warranty expiration if period changed
        if (isset($validated['warranty_period']) && !isset($validated['warranty_expires'])) {
            $validated['warranty_expires'] = $this->calculateWarrantyExpiration($validated['warranty_period']);
        }

        $oldTotalCost = $repair->total_cost;
        $repair->update($validated);
        
        // Update maintenance request actual cost if repair cost changed
        if (isset($validated['total_cost']) && $validated['total_cost'] != $oldTotalCost) {
            $repair->maintenanceRequest->update(['actual_cost' => $validated['total_cost']]);
        }
        
        return response()->json($repair);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Repair $repair)
    {
        DB::beginTransaction();
        
        try {
            // Reset maintenance request actual cost
            $maintenanceRequest = $repair->maintenanceRequest;
            $maintenanceRequest->update([
                'actual_cost' => null,
                'status' => 'in_progress'
            ]);
            
            $repair->delete();
            
            DB::commit();
            
            return response()->json(null, Response::HTTP_NO_CONTENT);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete repair record',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get repairs for a specific maintenance request.
     */
    public function byMaintenanceRequest(MaintenanceRequest $maintenanceRequest)
    {
        $repairs = $maintenanceRequest->repairs()
            ->with('vendor')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($repairs);
    }

    /**
     * Get repairs for a specific vendor.
     */
    public function byVendor(Vendor $vendor)
    {
        $repairs = $vendor->repairs()
            ->with(['maintenanceRequest', 'maintenanceRequest.unit'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($repairs);
    }

    /**
     * Get repairs with active warranty.
     */
    public function activeWarranties(Request $request)
    {
        $query = Repair::with(['maintenanceRequest.unit', 'vendor'])
            ->whereNotNull('warranty_expires')
            ->where('warranty_expires', '>=', now());
        
        if ($request->has('expiring_soon')) {
            $days = $request->get('days', 30);
            $query->where('warranty_expires', '<=', now()->addDays($days));
        }
        
        $repairs = $query->orderBy('warranty_expires')->paginate(20);
        return response()->json($repairs);
    }

    /**
     * Get repair statistics.
     */
    public function statistics(Request $request)
    {
        $query = Repair::query();
        
        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_repairs' => $query->count(),
            'total_cost' => $query->sum('total_cost'),
            'average_cost' => $query->avg('total_cost'),
            'total_labor_hours' => $query->sum('labor_hours'),
            'total_parts_cost' => $query->sum('parts_cost'),
            'repairs_by_type' => $query->clone()
                ->select('repair_type', \DB::raw('count(*) as count, sum(total_cost) as total_cost'))
                ->groupBy('repair_type')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->repair_type => [
                        'count' => $item->count,
                        'total_cost' => $item->total_cost
                    ]];
                }),
            'top_vendors' => $query->clone()
                ->join('vendors', 'repairs.vendor_id', '=', 'vendors.id')
                ->select('vendors.company_name', \DB::raw('count(*) as repair_count, sum(repairs.total_cost) as total_spent'))
                ->groupBy('vendor_id', 'vendors.company_name')
                ->orderBy('repair_count', 'desc')
                ->limit(10)
                ->get(),
            'warranty_stats' => [
                'active_warranties' => $query->clone()
                    ->whereNotNull('warranty_expires')
                    ->where('warranty_expires', '>=', now())
                    ->count(),
                'expired_warranties' => $query->clone()
                    ->whereNotNull('warranty_expires')
                    ->where('warranty_expires', '<', now())
                    ->count(),
                'expiring_soon' => $query->clone()
                    ->whereNotNull('warranty_expires')
                    ->where('warranty_expires', '>=', now())
                    ->where('warranty_expires', '<=', now()->addDays(30))
                    ->count(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Calculate cost breakdown for a repair.
     */
    public function costBreakdown(Repair $repair)
    {
        $laborCost = ($repair->labor_hours ?? 0) * ($repair->labor_rate ?? 0);
        $partsCost = $repair->parts_cost ?? 0;
        $otherCost = $repair->total_cost - ($laborCost + $partsCost);
        
        $breakdown = [
            'labor' => [
                'hours' => $repair->labor_hours,
                'rate' => $repair->labor_rate,
                'cost' => $laborCost,
                'percentage' => $repair->total_cost > 0 ? ($laborCost / $repair->total_cost) * 100 : 0
            ],
            'parts' => [
                'cost' => $partsCost,
                'percentage' => $repair->total_cost > 0 ? ($partsCost / $repair->total_cost) * 100 : 0,
                'parts_list' => $repair->parts_used ? explode(', ', $repair->parts_used) : []
            ],
            'other' => [
                'cost' => $otherCost,
                'percentage' => $repair->total_cost > 0 ? ($otherCost / $repair->total_cost) * 100 : 0
            ],
            'total' => $repair->total_cost
        ];
        
        return response()->json($breakdown);
    }

    /**
     * Extend warranty for a repair.
     */
    public function extendWarranty(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'additional_period' => 'required|string|max:100',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $newExpiration = $this->calculateWarrantyExpiration(
            $validated['additional_period'],
            $repair->warranty_expires ?? now()
        );
        
        $repair->update([
            'warranty_expires' => $newExpiration,
            'warranty_period' => $repair->warranty_period . ' + ' . $validated['additional_period']
        ]);
        
        // Log warranty extension
        // ActivityLog::create([...]);
        
        return response()->json($repair);
    }

    /**
     * Calculate warranty expiration date.
     */
    private function calculateWarrantyExpiration($warrantyPeriod, $startDate = null)
    {
        $start = $startDate ? \Carbon\Carbon::parse($startDate) : now();
        
        if (preg_match('/(\d+)\s*(day|week|month|year)s?/i', $warrantyPeriod, $matches)) {
            $number = (int)$matches[1];
            $unit = strtolower($matches[2]);
            
            return $start->add($unit . 's', $number);
        }
        
        // Default to 90 days if format not recognized
        return $start->addDays(90);
    }

    /**
     * Import repair photos.
     */
    public function importPhotos(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'before_photos' => 'nullable|array',
            'before_photos.*' => 'url',
            'after_photos' => 'nullable|array',
            'after_photos.*' => 'url',
        ]);

        $currentBefore = $repair->before_photos ?? [];
        $currentAfter = $repair->after_photos ?? [];
        
        if (isset($validated['before_photos'])) {
            $currentBefore = array_merge($currentBefore, $validated['before_photos']);
        }
        
        if (isset($validated['after_photos'])) {
            $currentAfter = array_merge($currentAfter, $validated['after_photos']);
        }
        
        $repair->update([
            'before_photos' => $currentBefore,
            'after_photos' => $currentAfter
        ]);
        
        return response()->json($repair);
    }

    /**
     * Get repair timeline for a unit.
     */
    public function unitRepairHistory(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
        ]);

        $query = Repair::with([
            'maintenanceRequest.maintenanceCategory',
            'vendor'
        ])->whereHas('maintenanceRequest', function($q) use ($validated) {
            $q->where('unit_id', $validated['unit_id']);
        });
        
        if (isset($validated['date_from'])) {
            $query->where('created_at', '>=', $validated['date_from']);
        }
        
        if (isset($validated['date_to'])) {
            $query->where('created_at', '<=', $validated['date_to']);
        }
        
        $repairs = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($repairs);
    }

    /**
     * Generate repair report.
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'vendor_id' => 'nullable|exists:vendors,id',
            'repair_type' => 'nullable|in:minor,major,emergency,replacement',
            'format' => 'nullable|in:pdf,csv,excel',
        ]);

        $query = Repair::with([
            'maintenanceRequest.unit',
            'maintenanceRequest.maintenanceCategory',
            'vendor'
        ])->whereBetween('created_at', [$validated['start_date'], $validated['end_date']]);
        
        if (isset($validated['vendor_id'])) {
            $query->where('vendor_id', $validated['vendor_id']);
        }
        
        if (isset($validated['repair_type'])) {
            $query->where('repair_type', $validated['repair_type']);
        }
        
        $repairs = $query->get();
        
        $reportData = [
            'period' => [
                'start' => $validated['start_date'],
                'end' => $validated['end_date']
            ],
            'summary' => [
                'total_repairs' => $repairs->count(),
                'total_cost' => $repairs->sum('total_cost'),
                'average_cost' => $repairs->avg('total_cost'),
                'total_labor_hours' => $repairs->sum('labor_hours'),
            ],
            'by_repair_type' => $repairs->groupBy('repair_type')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('total_cost'),
                    'average_cost' => $group->avg('total_cost')
                ];
            }),
            'by_vendor' => $repairs->groupBy('vendor.company_name')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_cost' => $group->sum('total_cost'),
                    'average_cost' => $group->avg('total_cost')
                ];
            }),
            'repairs' => $repairs->map(function($repair) {
                return [
                    'id' => $repair->id,
                    'date' => $repair->created_at->format('Y-m-d'),
                    'unit' => $repair->maintenanceRequest->unit->unit_number ?? 'N/A',
                    'category' => $repair->maintenanceRequest->maintenanceCategory->name ?? 'N/A',
                    'vendor' => $repair->vendor->company_name ?? 'N/A',
                    'type' => $repair->repair_type,
                    'total_cost' => $repair->total_cost,
                    'labor_hours' => $repair->labor_hours,
                    'parts_cost' => $repair->parts_cost,
                    'warranty_expires' => $repair->warranty_expires,
                ];
            })
        ];
        
        // In a real application, you would generate PDF/CSV/Excel here
        // For now, return JSON
        
        return response()->json($reportData);
    }
}