<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Building;
use App\Models\Unit;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['building', 'unit', 'currentLease']);
        
        // Filter by building if selected
        if ($request->has('building_id') && $request->building_id != '') {
            $query->where('building_id', $request->building_id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }
        
        $tenants = $query->latest()->paginate(20);
        
        // Calculate statistics
        $activeCount = Tenant::whereHas('currentLease', function($q) {
            $q->where('lease_status', 'active');
        })->count();
        
        $expiringSoonCount = Tenant::whereHas('currentLease', function($q) {
            $q->where('lease_status', 'active')
              ->whereDate('end_date', '<=', now()->addDays(30));
        })->count();
        
        $totalMonthlyRent = Tenant::whereHas('currentLease', function($q) {
            $q->where('lease_status', 'active');
        })->with('currentLease')->get()->sum(function($tenant) {
            return $tenant->currentLease?->monthly_rent ?? 0;
        });
        
        $buildings = Building::orderBy('name')->get();
        
        return view('tenants.index', compact('tenants', 'activeCount', 'expiringSoonCount', 'totalMonthlyRent', 'buildings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $buildings = Building::orderBy('name')->get();
        
        // Check if building ID was passed in query string
        $selectedBuilding = null;
        $selectedUnit = null;
        
        if ($request->has('building_id')) {
            $selectedBuilding = Building::find($request->building_id);
        }
        
        if ($request->has('unit_id')) {
            $selectedUnit = Unit::with('building')->find($request->unit_id);
            if ($selectedUnit && !$selectedBuilding) {
                $selectedBuilding = $selectedUnit->building;
            }
        }
        
        return view('tenants.create', compact('buildings', 'selectedBuilding', 'selectedUnit'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // DEBUG: Log the incoming request
        Log::info('TENANT STORE - Request received', [
            'all_data' => $request->all(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);
        
        try {
            // Simplified validation first to see if we get past validation
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:tenants,email',
                'phone' => 'required|string|max:20',
                'building_id' => 'required|exists:buildings,id',
                'unit_id' => 'required|exists:units,id',
                'lease_start_date' => 'required|date',
                'lease_end_date' => 'required|date|after:lease_start_date',
                'monthly_rent' => 'required|numeric|min:0',
                'lease_status' => 'required|in:active,pending,expired,terminated',
            ]);
            
            Log::info('TENANT STORE - Validation passed', $validated);
            
            // Set default values for optional fields
            $validated['is_active'] = $request->has('is_active');
            $validated['number_of_occupants'] = $request->input('number_of_occupants', 1);
            $validated['security_deposit'] = $request->input('security_deposit', 0);
            
            // Add optional fields if they exist
            $optionalFields = [
                'alternate_phone', 'date_of_birth', 'emergency_contact_name', 
                'emergency_contact_phone', 'emergency_contact_relation', 'id_type',
                'occupation', 'employer', 'annual_income', 'notes'
            ];
            
            foreach ($optionalFields as $field) {
                if ($request->has($field)) {
                    $validated[$field] = $request->input($field);
                }
            }
            
            DB::beginTransaction();
            
            // Create the tenant
            $tenant = Tenant::create($validated);
            
            Log::info('TENANT STORE - Tenant created', ['tenant_id' => $tenant->id]);
            
            // Get the unit
            $unit = Unit::with('building')->find($tenant->unit_id);
            
            if (!$unit) {
                throw new \Exception('Unit not found with ID: ' . $tenant->unit_id);
            }
            
            // Create lease record
            $lease = Lease::create([
                'tenant_id' => $tenant->id,
                'unit_id' => $tenant->unit_id,
                'start_date' => $tenant->lease_start_date,
                'end_date' => $tenant->lease_end_date,
                'move_in_date' => $tenant->lease_start_date,
                'monthly_rent' => $tenant->monthly_rent,
                'security_deposit' => $tenant->security_deposit ?? 0,
                'lease_status' => $tenant->lease_status,
                'lease_type' => 'Standard',
                'payment_due_day' => 1,
                'lease_number' => 'LS-' . strtoupper(uniqid()),
            ]);
            
            Log::info('TENANT STORE - Lease created', ['lease_id' => $lease->id]);
            
            // Update unit status if lease is active
            if ($lease->lease_status === 'active') {
                $unit->update(['status' => 'occupied']);
            } elseif ($lease->lease_status === 'pending') {
                $unit->update(['status' => 'reserved']);
            }
            
            DB::commit();
            
            Log::info('TENANT STORE - Success!', ['tenant' => $tenant->full_name]);
            
            return redirect()->route('tenants.show', $tenant)
                ->with('success', 'Tenant "' . $tenant->full_name . '" created successfully!');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('TENANT STORE - Validation failed', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TENANT STORE - Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Failed to create tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['building', 'unit', 'currentLease', 'leases']);
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        $tenant->load(['building', 'unit', 'currentLease']);
        $buildings = Building::orderBy('name')->get();
        return view('tenants.edit', compact('tenant', 'buildings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        // Similar simplified update logic
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email,' . $tenant->id,
            'phone' => 'required|string|max:20',
            'building_id' => 'required|exists:buildings,id',
            'unit_id' => 'required|exists:units,id',
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after:lease_start_date',
            'monthly_rent' => 'required|numeric|min:0',
            'lease_status' => 'required|in:active,pending,expired,terminated',
        ]);
        
        try {
            DB::beginTransaction();
            
            $validated['is_active'] = $request->has('is_active');
            $tenant->update($validated);
            
            // Update or create lease
            $lease = $tenant->currentLease;
            if ($lease) {
                $lease->update([
                    'start_date' => $validated['lease_start_date'],
                    'end_date' => $validated['lease_end_date'],
                    'monthly_rent' => $validated['monthly_rent'],
                    'lease_status' => $validated['lease_status'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('tenants.show', $tenant)
                ->with('success', 'Tenant updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tenant update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        try {
            $tenant->delete();
            return redirect()->route('tenants.index')
                ->with('success', 'Tenant deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('tenants.show', $tenant)
                ->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Get units by building (AJAX)
     */
    public function getUnitsByBuilding($buildingId)
    {
        $units = Unit::where('building_id', $buildingId)
                    ->whereIn('status', ['vacant', 'ready'])
                    ->orderBy('unit_number')
                    ->get(['id', 'unit_number', 'monthly_rent', 'unit_type', 'status']);
        
        return response()->json($units);
    }

    /**
     * Check if a unit already has an active tenant
     */
    public function checkUnitAvailability(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'building_id' => 'required|exists:buildings,id'
        ]);

        $activeTenant = Tenant::where('unit_id', $request->unit_id)
            ->whereHas('currentLease', function($q) {
                $q->whereIn('lease_status', ['active', 'pending']);
            })
            ->first();

        if ($activeTenant) {
            return response()->json([
                'has_active_tenant' => true,
                'tenant_name' => $activeTenant->full_name,
                'lease_end_date' => $activeTenant->currentLease?->end_date?->format('M d, Y'),
            ]);
        }

        return response()->json([
            'has_active_tenant' => false,
        ]);
    }

    /**
     * Export tenants to CSV
     */
    public function export(Request $request)
    {
        $query = Tenant::with(['building', 'unit']);
        
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        
        $tenants = $query->get();
        
        $filename = 'tenants_export_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($tenants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Phone', 'Building', 'Unit', 'Monthly Rent', 'Status']);
            
            foreach ($tenants as $tenant) {
                fputcsv($file, [
                    $tenant->full_name,
                    $tenant->email,
                    $tenant->phone,
                    $tenant->building->name ?? '',
                    $tenant->unit->unit_number ?? '',
                    $tenant->monthly_rent,
                    $tenant->lease_status_label ?? 'Active',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Restore soft deleted tenant
     */
    public function restore($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();
        
        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant restored successfully!');
    }

    /**
     * Force delete tenant
     */
    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->forceDelete();
        
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant permanently deleted!');
    }
}