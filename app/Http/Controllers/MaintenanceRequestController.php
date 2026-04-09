<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Building;
use App\Models\MaintenanceCategory;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MaintenanceRequest::with([
            'unit', 
            'unit.building',
            'tenant', 
            'maintenanceCategory', 
            'assignedVendor', 
            'assignedStaff'
        ]);
        
        // Filter by building if specified
        if ($request->has('building_id') && $request->building_id) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        
        // Filter by unit
        if ($request->has('unit_id') && $request->unit_id) {
            $query->where('unit_id', $request->unit_id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }
        
        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('maintenance-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Get data from database
        $buildings = Building::where('status', 'active')->orderBy('name')->get();
        $categories = MaintenanceCategory::orderBy('name')->get(); // FIXED: This loads categories from database
        $vendors = Vendor::orderBy('company_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();
        
        // Pre-select building if provided
        $selectedBuilding = null;
        if ($request->has('building_id')) {
            $selectedBuilding = Building::find($request->building_id);
        }
        
        // Pre-select unit if provided
        $selectedUnit = null;
        if ($request->has('unit_id')) {
            $selectedUnit = Unit::with('building')->find($request->unit_id);
        }
        
        // Get current tenant for the pre-selected unit
        $currentTenant = null;
        if ($selectedUnit) {
            $currentTenant = $selectedUnit->currentTenant;
        }
        
        return view('maintenance-requests.create', compact(
            'buildings', 
            'categories', 
            'vendors', 
            'staff',
            'selectedBuilding',
            'selectedUnit',
            'currentTenant'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'required|exists:units,id',
                'tenant_id' => 'nullable|exists:tenants,id',
                'maintenance_category_id' => 'required|exists:maintenance_categories,id',
                'title' => 'required|string|max:200',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high,emergency',
                'request_date' => 'required|date',
                'scheduled_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric|min:0',
                'assigned_vendor_id' => 'nullable|exists:vendors,id',
                'assigned_staff_id' => 'nullable|exists:users,id',
                'access_instructions' => 'nullable|string',
                'internal_notes' => 'nullable|string',
            ]);

            // Set default status
            $validated['status'] = 'submitted';
            
            // If tenant_id not provided, try to get current tenant of the unit
            if (empty($validated['tenant_id'])) {
                $unit = Unit::with('currentTenant')->find($validated['unit_id']);
                if ($unit && $unit->currentTenant) {
                    $validated['tenant_id'] = $unit->currentTenant->id;
                }
            }
            
            $maintenanceRequest = MaintenanceRequest::create($validated);
            
            return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                             ->with('success', 'Maintenance request created successfully.');
                             
        } catch (\Exception $e) {
            Log::error('Failed to create maintenance request: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load([
            'unit', 
            'unit.building',
            'unit.currentTenant',
            'tenant',
            'maintenanceCategory', 
            'assignedVendor', 
            'assignedStaff',
            'repairs'
        ]);
        
        return view('maintenance-requests.show', compact('maintenanceRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->load(['unit', 'unit.building', 'unit.currentTenant', 'tenant']);
        
        $buildings = Building::where('status', 'active')->orderBy('name')->get();
        $categories = MaintenanceCategory::orderBy('name')->get();
        $vendors = Vendor::orderBy('company_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();
        
        return view('maintenance-requests.edit', compact(
            'maintenanceRequest',
            'buildings', 
            'categories', 
            'vendors', 
            'staff'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        try {
            $validated = $request->validate([
                'unit_id' => 'sometimes|required|exists:units,id',
                'tenant_id' => 'nullable|exists:tenants,id',
                'maintenance_category_id' => 'sometimes|required|exists:maintenance_categories,id',
                'title' => 'sometimes|required|string|max:200',
                'description' => 'sometimes|required|string',
                'priority' => 'sometimes|required|in:low,medium,high,emergency',
                'status' => 'sometimes|in:submitted,assigned,in_progress,completed,cancelled',
                'request_date' => 'sometimes|required|date',
                'scheduled_date' => 'nullable|date',
                'completion_date' => 'nullable|date',
                'estimated_cost' => 'nullable|numeric|min:0',
                'actual_cost' => 'nullable|numeric|min:0',
                'assigned_vendor_id' => 'nullable|exists:vendors,id',
                'assigned_staff_id' => 'nullable|exists:users,id',
                'resolution_notes' => 'nullable|string',
                'tenant_rating' => 'nullable|integer|min:1|max:5',
                'tenant_feedback' => 'nullable|string',
                'access_instructions' => 'nullable|string',
                'internal_notes' => 'nullable|string',
            ]);

            // Set completion date if status changed to completed
            if (isset($validated['status']) && $validated['status'] === 'completed' && !$maintenanceRequest->completion_date) {
                $validated['completion_date'] = now();
            }

            // Set assigned date if status changed to assigned
            if (isset($validated['status']) && $validated['status'] === 'assigned' && !$maintenanceRequest->assigned_date) {
                $validated['assigned_date'] = now();
            }

            $maintenanceRequest->update($validated);
            
            return redirect()->route('maintenance-requests.show', $maintenanceRequest)
                             ->with('success', 'Maintenance request updated successfully.');
                             
        } catch (\Exception $e) {
            Log::error('Failed to update maintenance request: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        try {
            $maintenanceRequest->delete();
            
            return redirect()->route('maintenance-requests.index')
                             ->with('success', 'Maintenance request deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete maintenance request: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Get units by building (AJAX)
     */
    public function getUnitsByBuilding($buildingId)
    {
        $units = Unit::where('building_id', $buildingId)
                    ->orderBy('unit_number')
                    ->get(['id', 'unit_number', 'unit_type', 'monthly_rent', 'status']);
        
        return response()->json($units);
    }

    /**
     * Get tenants for a unit (AJAX)
     */
    public function getTenantsByUnit($unitId)
    {
        $unit = Unit::with('currentTenant')->find($unitId);
        
        if (!$unit || !$unit->currentTenant) {
            return response()->json([]);
        }
        
        return response()->json([
            [
                'id' => $unit->currentTenant->id,
                'full_name' => $unit->currentTenant->full_name,
                'email' => $unit->currentTenant->email,
                'phone' => $unit->currentTenant->phone,
            ]
        ]);
    }

    /**
     * Get open maintenance requests.
     */
    public function open(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderByRaw("CASE priority 
                WHEN 'emergency' THEN 1 
                WHEN 'high' THEN 2 
                WHEN 'medium' THEN 3 
                WHEN 'low' THEN 4 
                END")
            ->orderBy('request_date', 'asc')
            ->paginate(15);
            
        return view('maintenance-requests.index', compact('requests'));
    }

    /**
     * Get overdue maintenance requests.
     */
    public function overdue(Request $request)
    {
        $requests = MaintenanceRequest::with(['unit', 'unit.building', 'maintenanceCategory'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereHas('maintenanceCategory', function($q) {
                $q->whereNotNull('sla_hours');
            })
            ->get()
            ->filter(function($request) {
                $slaHours = $request->maintenanceCategory->sla_hours;
                $hoursSinceRequest = $request->request_date->diffInHours(now());
                return $hoursSinceRequest > $slaHours;
            });

        // Paginate the filtered collection
        $page = $request->get('page', 1);
        $perPage = 15;
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $requests->forPage($page, $perPage)->values(),
            $requests->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('maintenance-requests.index', ['requests' => $paginated]);
    }
}