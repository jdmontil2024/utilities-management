<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Building;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Unit::with(['building', 'currentTenant', 'currentLease']);
        
        // Filter by building if selected
        if ($request->has('building_id') && $request->building_id != '') {
            $query->where('building_id', $request->building_id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('unit_number', 'like', '%' . $search . '%')
                  ->orWhere('unit_name', 'like', '%' . $search . '%')
                  ->orWhereHas('building', function($b) use ($search) {
                      $b->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by unit type
        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }
        
        // Sort
        $sortField = $request->get('sort', 'building_id');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection)
              ->orderBy('unit_number', 'asc');
        
        $units = $query->paginate(12)->withQueryString();
        
        // Get all buildings for filter dropdown
        $buildings = Building::withCount('units')
                            ->orderBy('name')
                            ->get();
        
        // Get selected building for view
        $selectedBuilding = null;
        if ($request->has('building_id') && $request->building_id != '') {
            $selectedBuilding = Building::find($request->building_id);
        }
        
        return view('units.index', compact('units', 'buildings', 'selectedBuilding'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buildings = Building::orderBy('name')->get();
        
        // Check if building ID was passed in query string
        $selectedBuilding = null;
        if (request()->has('building_id')) {
            $selectedBuilding = Building::find(request('building_id'));
        }
        
        return view('units.create', compact('buildings', 'selectedBuilding'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'unit_number' => 'required|string|max:50',
            'unit_name' => 'nullable|string|max:100',
            'floor' => 'required|integer',
            'area' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|numeric|min:0',
            'unit_type' => 'required|string',
            'status' => 'required|string',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'parking_fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'year_renovated' => 'nullable|integer|min:1900|max:' . date('Y'),
            'available_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate unit number in the same building
        $existingUnit = Unit::where('building_id', $validated['building_id'])
                            ->where('unit_number', $validated['unit_number'])
                            ->first();
        
        if ($existingUnit) {
            return back()->withInput()->withErrors([
                'unit_number' => 'A unit with this number already exists in the selected building.'
            ]);
        }

        // Handle features and amenities arrays
        $validated['features'] = $request->has('features') ? $request->input('features') : [];
        $validated['amenities'] = $request->has('amenities') ? $request->input('amenities') : [];

        try {
            DB::beginTransaction();
            
            // Create the unit
            $unit = Unit::create($validated);
            
            DB::commit();
            
            // Redirect back to filtered list if coming from building filter
            if ($request->has('from_building')) {
                return redirect()->route('units.index', ['building_id' => $unit->building_id])
                    ->with('success', 'Unit created successfully!');
            }
            
            return redirect()->route('units.show', $unit)
                ->with('success', 'Unit created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Failed to create unit: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        // Load the unit with ALL necessary relationships for the show page
        $unit->load([
            'building',
            'currentTenant',
            'currentLease',
            'leases' => function ($query) {
                $query->with('tenant')->latest()->limit(10);
            },
            'maintenanceRequests' => function ($query) {
                $query->latest()->limit(10);
            },
            'meterReadings' => function ($query) {
                $query->latest()->limit(10);
            },
            'preventiveMaintenances',
            'consumptions'
        ]);

        // Add empty bills collection to avoid errors (temporary fix)
        $unit->setRelation('bills', collect([]));

        return view('units.show', compact('unit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        // Get all buildings for the dropdown
        $buildings = Building::orderBy('name')->get();
        
        return view('units.edit', compact('unit', 'buildings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        // Validate the request
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'unit_number' => 'required|string|max:50',
            'unit_name' => 'nullable|string|max:100',
            'floor' => 'required|integer',
            'area' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|numeric|min:0',
            'unit_type' => 'required|string',
            'status' => 'required|string',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'parking_fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'year_renovated' => 'nullable|integer|min:1900|max:' . date('Y'),
            'available_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Check for duplicate unit number in the same building (excluding current unit)
        $existingUnit = Unit::where('building_id', $validated['building_id'])
                            ->where('unit_number', $validated['unit_number'])
                            ->where('id', '!=', $unit->id)
                            ->first();
        
        if ($existingUnit) {
            return back()->withInput()->withErrors([
                'unit_number' => 'A unit with this number already exists in the selected building.'
            ]);
        }

        // Handle features and amenities arrays
        $validated['features'] = $request->has('features') ? $request->input('features') : [];
        $validated['amenities'] = $request->has('amenities') ? $request->input('amenities') : [];

        try {
            DB::beginTransaction();
            
            // Update the unit
            $unit->update($validated);
            
            DB::commit();
            
            // Check if we should redirect back to filtered index
            if ($request->has('return_to_index')) {
                return redirect()->route('units.index', ['building_id' => $unit->building_id])
                    ->with('success', 'Unit updated successfully!');
            }
            
            return redirect()->route('units.show', $unit)
                ->with('success', 'Unit updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()->with('error', 'Failed to update unit: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        try {
            // Check if unit has any active tenants
            if ($unit->currentTenant()->exists()) {
                return redirect()->route('units.show', $unit)
                    ->with('error', 'Cannot delete unit that has an active tenant. Please end the lease first.');
            }
            
            // Check if unit has any leases
            if ($unit->leases()->exists()) {
                return redirect()->route('units.show', $unit)
                    ->with('error', 'Cannot delete unit that has historical leases.');
            }
            
            // Check if unit has any maintenance requests
            if ($unit->maintenanceRequests()->exists()) {
                return redirect()->route('units.show', $unit)
                    ->with('error', 'Cannot delete unit that has maintenance requests.');
            }
            
            // Store building_id for redirect before delete
            $buildingId = $unit->building_id;
            
            // Soft delete the unit
            $unit->delete();
            
            // Check if we should redirect back to filtered index
            if (request()->has('return_to_index')) {
                return redirect()->route('units.index', ['building_id' => $buildingId])
                    ->with('success', 'Unit deleted successfully.');
            }
            
            return redirect()->route('units.index')
                ->with('success', 'Unit deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('units.show', $unit)
                ->with('error', 'Failed to delete unit: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted unit.
     */
    public function restore($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        
        try {
            $unit->restore();
            
            return redirect()->route('units.show', $unit)
                ->with('success', 'Unit restored successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('units.index')
                ->with('error', 'Failed to restore unit: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a unit.
     */
    public function forceDelete($id)
    {
        $unit = Unit::withTrashed()->findOrFail($id);
        
        try {
            // Check if unit has any related records
            if ($unit->leases()->withTrashed()->exists() || 
                $unit->maintenanceRequests()->withTrashed()->exists() ||
                $unit->tenants()->withTrashed()->exists()) {
                return redirect()->route('units.index')
                    ->with('error', 'Cannot permanently delete unit that has related records.');
            }
            
            $unit->forceDelete();
            
            return redirect()->route('units.index')
                ->with('success', 'Unit permanently deleted.');
                
        } catch (\Exception $e) {
            return redirect()->route('units.index')
                ->with('error', 'Failed to permanently delete unit: ' . $e->getMessage());
        }
    }

    /**
     * Show units by building.
     */
    public function byBuilding(Building $building)
    {
        $units = $building->units()
                    ->with(['currentTenant', 'currentLease'])
                    ->orderBy('floor')
                    ->orderBy('unit_number')
                    ->paginate(20);
        
        return view('units.index', compact('units', 'building'));
    }

    /**
     * Get units for a specific building (for API/AJAX).
     */
    public function getUnitsByBuilding($buildingId)
    {
        $units = Unit::where('building_id', $buildingId)
                    ->orderBy('unit_number')
                    ->get(['id', 'unit_number', 'unit_name', 'status', 'monthly_rent']);
        
        return response()->json($units);
    }

    /**
     * Get tenants for a specific unit (for AJAX dropdown in maintenance requests)
     */
    public function getTenants(Unit $unit)
    {
        $tenants = $unit->tenants()
            ->where('lease_status', 'active')
            ->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('lease_end_date')
                  ->orWhere('lease_end_date', '>=', now());
            })
            ->select('id', 'first_name', 'last_name', 'email', 'phone')
            ->orderBy('first_name')
            ->get()
            ->map(function($tenant) {
                return [
                    'id' => $tenant->id,
                    'full_name' => $tenant->full_name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                ];
            });
        
        return response()->json($tenants);
    }

    /**
     * Check if a unit number already exists in a building (AJAX)
     * UPDATED to properly detect occupied units
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'unit_number' => 'required|string'
        ]);

        // Check for existing active units (not soft-deleted)
        $existingUnit = Unit::where('building_id', $request->building_id)
                            ->where('unit_number', $request->unit_number)
                            ->first();

        if ($existingUnit) {
            // Unit exists - check if it's occupied using the currentTenant relationship
            $hasActiveTenant = $existingUnit->currentTenant()->exists();
            
            return response()->json([
                'exists' => true,
                'occupied' => $hasActiveTenant,
                'status' => $existingUnit->status,
                'message' => $hasActiveTenant 
                    ? 'This unit number is already taken and currently occupied' 
                    : 'This unit number is already taken'
            ]);
        }

        // Check for soft-deleted units
        $softDeleted = Unit::where('building_id', $request->building_id)
                           ->where('unit_number', $request->unit_number)
                           ->withTrashed()
                           ->exists();

        return response()->json([
            'exists' => false,
            'occupied' => false,
            'soft_deleted' => $softDeleted,
            'message' => $softDeleted 
                ? 'This unit number was previously used but is deleted' 
                : 'Unit number is available'
        ]);
    }

    /**
     * Get units by building for display with full details (AJAX)
     */
    public function getByBuilding($buildingId)
    {
        $units = Unit::where('building_id', $buildingId)
                     ->withTrashed()
                     ->orderBy('unit_number')
                     ->get()
                     ->map(function($unit) {
                         return [
                             'id' => $unit->id,
                             'unit_number' => $unit->unit_number,
                             'unit_name' => $unit->unit_name,
                             'unit_type' => $unit->unit_type,
                             'unit_type_label' => $unit->unit_type_label ?? ucfirst($unit->unit_type),
                             'bedrooms' => $unit->bedrooms,
                             'bathrooms' => $unit->bathrooms,
                             'area' => $unit->area,
                             'status' => $unit->status,
                             'deleted_at' => $unit->deleted_at,
                         ];
                     });
        
        return response()->json($units);
    }

    /**
     * Search units.
     */
    public function search(Request $request)
    {
        $query = Unit::query()->with(['building', 'currentTenant', 'currentLease']);
        
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        
        if ($request->filled('unit_number')) {
            $query->where('unit_number', 'like', '%' . $request->unit_number . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('unit_type')) {
            $query->where('unit_type', $request->unit_type);
        }
        
        if ($request->filled('min_rent')) {
            $query->where('monthly_rent', '>=', $request->min_rent);
        }
        
        if ($request->filled('max_rent')) {
            $query->where('monthly_rent', '<=', $request->max_rent);
        }
        
        if ($request->filled('min_bedrooms')) {
            $query->where('bedrooms', '>=', $request->min_bedrooms);
        }
        
        if ($request->filled('min_bathrooms')) {
            $query->where('bathrooms', '>=', $request->min_bathrooms);
        }
        
        if ($request->filled('available_from')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('available_date')
                  ->orWhere('available_date', '<=', $request->available_from);
            });
        }
        
        $units = $query->orderBy('building_id')
                      ->orderBy('unit_number')
                      ->paginate(20)
                      ->appends($request->except('page'));
        
        $buildings = Building::orderBy('name')->get();
        
        return view('units.index', compact('units', 'buildings'));
    }

    /**
     * Get unit statistics.
     */
    public function statistics()
    {
        $totalUnits = Unit::count();
        $occupiedUnits = Unit::where('status', 'occupied')->count();
        $vacantUnits = Unit::where('status', 'vacant')->count();
        $maintenanceUnits = Unit::whereIn('status', ['maintenance', 'under_maintenance'])->count();
        
        $averageRent = Unit::avg('monthly_rent');
        $totalMonthlyRent = Unit::sum('monthly_rent');
        
        $unitsByType = Unit::select('unit_type', DB::raw('count(*) as count'))
                          ->groupBy('unit_type')
                          ->orderBy('count', 'desc')
                          ->get();
        
        $unitsByStatus = Unit::select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->orderBy('count', 'desc')
                            ->get();
        
        $unitsByBuilding = Building::withCount('units')
                                  ->having('units_count', '>', 0)
                                  ->orderBy('units_count', 'desc')
                                  ->get();
        
        return view('units.statistics', compact(
            'totalUnits',
            'occupiedUnits',
            'vacantUnits',
            'maintenanceUnits',
            'averageRent',
            'totalMonthlyRent',
            'unitsByType',
            'unitsByStatus',
            'unitsByBuilding'
        ));
    }

    /**
     * Export units to CSV.
     */
    public function export(Request $request)
    {
        $query = Unit::with(['building', 'currentTenant']);
        
        // Filter by building if specified
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        
        $units = $query->orderBy('building_id')
                      ->orderBy('unit_number')
                      ->get();
        
        $filename = 'units_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($units) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Building',
                'Unit Number',
                'Unit Name',
                'Floor',
                'Size (sq ft)',
                'Bedrooms',
                'Bathrooms',
                'Type',
                'Status',
                'Monthly Rent',
                'Security Deposit',
                'Parking Fee',
                'Available Date',
                'Current Tenant',
                'Tenant Email',
                'Tenant Phone',
                'Lease Start',
                'Lease End'
            ]);
            
            // Data
            foreach ($units as $unit) {
                fputcsv($file, [
                    $unit->building->name ?? '',
                    $unit->unit_number,
                    $unit->unit_name ?? '',
                    $unit->floor,
                    $unit->area,
                    $unit->bedrooms,
                    $unit->bathrooms,
                    $unit->unit_type ?? '',
                    $unit->status ?? '',
                    $unit->monthly_rent,
                    $unit->security_deposit,
                    $unit->parking_fee,
                    $unit->available_date ? $unit->available_date->format('Y-m-d') : '',
                    $unit->currentTenant ? $unit->currentTenant->full_name : '',
                    $unit->currentTenant ? $unit->currentTenant->email : '',
                    $unit->currentTenant ? $unit->currentTenant->phone : '',
                    $unit->currentTenant && $unit->currentTenant->lease_start_date 
                        ? $unit->currentTenant->lease_start_date->format('Y-m-d') 
                        : '',
                    $unit->currentTenant && $unit->currentTenant->lease_end_date 
                        ? $unit->currentTenant->lease_end_date->format('Y-m-d') 
                        : '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update unit status based on current leases
     */
    public function refreshStatus(Unit $unit)
    {
        try {
            $unit->updateStatusFromLeases();
            
            return redirect()->back()
                ->with('success', 'Unit status refreshed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to refresh unit status: ' . $e->getMessage());
        }
    }

    /**
     * Batch refresh status for all units
     */
    public function batchRefreshStatus()
    {
        try {
            $count = 0;
            $units = Unit::all();
            
            foreach ($units as $unit) {
                $oldStatus = $unit->status;
                $unit->updateStatusFromLeases();
                if ($unit->wasChanged('status')) {
                    $count++;
                }
            }
            
            return redirect()->back()
                ->with('success', "Refreshed status for {$count} units.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to refresh unit statuses: ' . $e->getMessage());
        }
    }
}