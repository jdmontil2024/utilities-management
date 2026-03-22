<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LeaseController extends Controller
{
    /**
     * Display a listing of all leases.
     */
    public function index(Request $request)
    {
        $query = Lease::with(['tenant', 'unit.building']);
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('lease_status', $request->status);
        }
        
        // Filter by building
        if ($request->has('building_id') && $request->building_id != '') {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('lease_number', 'like', "%{$search}%")
                  ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                      $tenantQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('unit', function($unitQuery) use ($search) {
                      $unitQuery->where('unit_number', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $leases = $query->paginate(20)->withQueryString();
        
        // Statistics
        $stats = [
            'active' => Lease::where('lease_status', 'active')->count(),
            'pending' => Lease::where('lease_status', 'pending')->count(),
            'expiring_30_days' => Lease::where('lease_status', 'active')
                ->whereDate('end_date', '>=', now())
                ->whereDate('end_date', '<=', now()->addDays(30))
                ->count(),
            'expired' => Lease::where('lease_status', 'expired')->count(),
            'terminated' => Lease::where('lease_status', 'terminated')->count(),
            'total_monthly_revenue' => Lease::where('lease_status', 'active')->sum('monthly_rent'),
        ];
        
        $buildings = Building::orderBy('name')->get();
        
        return view('leases.index', compact('leases', 'stats', 'buildings'));
    }

    /**
     * Show the form for creating a new lease with intelligent tenant selection.
     */
    public function create(Request $request)
    {
        $tenant = null;
        $unit = null;
        $buildings = Building::orderBy('name')->get();
        
        if ($request->has('tenant_id')) {
            $tenant = Tenant::with('currentLease')->find($request->tenant_id);
        }
        
        if ($request->has('unit_id')) {
            $unit = Unit::with('building')->find($request->unit_id);
        }
        
        // Intelligent tenant filtering
        $tenantsForNewLease = Tenant::whereDoesntHave('currentLease')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
            
        $tenantsForRenewal = Tenant::whereHas('currentLease')
            ->with('currentLease')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
        
        // Only show vacant or ready units
        $units = Unit::whereIn('status', ['vacant', 'ready'])
            ->with('building')
            ->orderBy('unit_number')
            ->get();
        
        return view('leases.create', compact(
            'tenantsForNewLease', 
            'tenantsForRenewal',
            'units', 
            'tenant', 
            'unit', 
            'buildings'
        ));
    }

    /**
     * Store a newly created lease with comprehensive validation.
     */
    public function store(Request $request)
    {
        // First, check if tenant exists
        $tenant = Tenant::find($request->tenant_id);
        
        if (!$tenant) {
            return back()->withInput()->with('error', 'Cannot create lease: Tenant not found.');
        }
        
        // Check if tenant already has an active lease (unless it's a renewal)
        if ($tenant->currentLease && $request->lease_type !== 'Renewal') {
            return back()->withInput()->with('error', 
                'Tenant "' . $tenant->full_name . '" already has an active lease ending ' . 
                $tenant->currentLease->end_date->format('M d, Y') . '. ' .
                'Please use the Renewal lease type or terminate the existing lease first.'
            );
        }
        
        // Check if unit is available
        $unit = Unit::find($request->unit_id);
        if (!$unit) {
            return back()->withInput()->with('error', 'Unit not found.');
        }
        
        if (!in_array($unit->status, ['vacant', 'ready'])) {
            return back()->withInput()->with('error', 
                'Unit ' . $unit->unit_number . ' is not available. Current status: ' . ucfirst($unit->status)
            );
        }
        
        // Validate the request
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'move_in_date' => 'nullable|date',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'lease_status' => 'required|in:active,pending,expired,terminated',
            'lease_type' => 'nullable|string|max:255',
            'terms' => 'nullable|json',
            'utilities_included' => 'nullable|json',
            'lease_agreement_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle file upload
            if ($request->hasFile('lease_agreement_path')) {
                $path = $request->file('lease_agreement_path')->store('leases/agreements', 'public');
                $validated['lease_agreement_path'] = $path;
            }

            // Get unit and building for lease number generation
            $unit = Unit::with('building')->find($validated['unit_id']);
            
            // Generate personalized lease number with year tracking
            $validated['lease_number'] = Lease::generateLeaseNumber(
                $unit->building->name,
                $unit->building_id,
                $validated['start_date']
            );
            
            // Set move_in_date from start_date if not provided
            if (!isset($validated['move_in_date']) || empty($validated['move_in_date'])) {
                $validated['move_in_date'] = $validated['start_date'];
            }

            // Create lease
            $lease = Lease::create($validated);

            // Update tenant's lease fields for backward compatibility
            $tenant = Tenant::find($validated['tenant_id']);
            $tenant->update([
                'unit_id' => $validated['unit_id'],
                'lease_start_date' => $validated['start_date'],
                'lease_end_date' => $validated['end_date'],
                'monthly_rent' => $validated['monthly_rent'],
                'security_deposit' => $validated['security_deposit'] ?? 0,
                'lease_status' => $validated['lease_status'],
            ]);

            // Update unit status based on lease status
            if ($validated['lease_status'] === 'active') {
                Unit::where('id', $validated['unit_id'])->update(['status' => 'occupied']);
            } elseif ($validated['lease_status'] === 'pending') {
                Unit::where('id', $validated['unit_id'])->update(['status' => 'reserved']);
            }

            DB::commit();

            Log::info('Lease created successfully', [
                'lease_id' => $lease->id,
                'lease_number' => $lease->lease_number,
                'status' => $lease->lease_status,
                'tenant_id' => $tenant->id
            ]);

            return redirect()->route('leases.show', $lease)
                ->with('success', 'Lease created successfully with status: ' . ucfirst($lease->lease_status));

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if it exists
            if (isset($validated['lease_agreement_path'])) {
                Storage::disk('public')->delete($validated['lease_agreement_path']);
            }
            
            Log::error('Failed to create lease: ' . $e->getMessage(), [
                'error' => $e,
                'request' => $request->all()
            ]);
            
            return back()->withInput()->with('error', 'Failed to create lease: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified lease.
     */
    public function show(Lease $lease)
    {
        $lease->load(['tenant', 'unit.building', 'bills']);
        
        return view('leases.show', compact('lease'));
    }

    /**
     * Show the form for editing the specified lease.
     */
    public function edit(Lease $lease)
    {
        $lease->load(['tenant', 'unit']);
        $tenants = Tenant::all();
        $units = Unit::with('building')->get();
        
        return view('leases.edit', compact('lease', 'tenants', 'units'));
    }

    /**
     * Update the specified lease.
     */
    public function update(Request $request, Lease $lease)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'move_in_date' => 'nullable|date',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'payment_due_day' => 'nullable|integer|min:1|max:31',
            'lease_status' => 'required|in:active,pending,expired,terminated',
            'lease_type' => 'nullable|string|max:255',
            'terms' => 'nullable|json',
            'utilities_included' => 'nullable|json',
            'lease_agreement_path' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $lease->lease_status;
            $oldUnitId = $lease->unit_id;

            // Handle file upload
            if ($request->hasFile('lease_agreement_path')) {
                // Delete old file
                if ($lease->lease_agreement_path) {
                    Storage::disk('public')->delete($lease->lease_agreement_path);
                }
                $path = $request->file('lease_agreement_path')->store('leases/agreements', 'public');
                $validated['lease_agreement_path'] = $path;
            }

            // Set move_in_date from start_date if not provided
            if (!isset($validated['move_in_date']) || empty($validated['move_in_date'])) {
                $validated['move_in_date'] = $validated['start_date'];
            }

            // Update lease
            $lease->update($validated);

            // Update tenant's lease fields for backward compatibility
            $lease->tenant->update([
                'unit_id' => $lease->unit_id,
                'lease_start_date' => $validated['start_date'],
                'lease_end_date' => $validated['end_date'],
                'monthly_rent' => $validated['monthly_rent'],
                'security_deposit' => $validated['security_deposit'] ?? 0,
                'lease_status' => $validated['lease_status'],
            ]);

            // Handle unit status changes
            if ($oldStatus !== $validated['lease_status']) {
                $unit = Unit::find($lease->unit_id);
                
                if ($validated['lease_status'] === 'active') {
                    $unit->update(['status' => 'occupied']);
                } elseif ($validated['lease_status'] === 'pending') {
                    $unit->update(['status' => 'reserved']);
                } elseif ($oldStatus === 'active' || $oldStatus === 'pending') {
                    // Check if unit has any other active or pending leases
                    $hasOtherActiveLease = Lease::where('unit_id', $unit->id)
                        ->where('id', '!=', $lease->id)
                        ->whereIn('lease_status', ['active', 'pending'])
                        ->exists();
                    
                    if (!$hasOtherActiveLease) {
                        $unit->update(['status' => 'vacant']);
                    }
                }
            }

            DB::commit();

            Log::info('Lease updated successfully', [
                'lease_id' => $lease->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['lease_status']
            ]);

            return redirect()->route('leases.show', $lease)
                ->with('success', 'Lease updated successfully. Status: ' . ucfirst($validated['lease_status']));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update lease: ' . $e->getMessage(), [
                'lease_id' => $lease->id,
                'error' => $e
            ]);
            return back()->withInput()->with('error', 'Failed to update lease: ' . $e->getMessage());
        }
    }

    /**
     * Renew a lease (create new lease from existing)
     */
    public function renew(Request $request, Lease $lease)
    {
        $validated = $request->validate([
            'new_end_date' => 'required|date|after:today',
            'new_monthly_rent' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $newLease = $lease->renew(
                $validated['new_end_date'],
                $validated['new_monthly_rent'] ?? $lease->monthly_rent
            );

            DB::commit();

            return redirect()->route('leases.show', $newLease)
                ->with('success', 'Lease renewed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to renew lease: ' . $e->getMessage());
        }
    }

    /**
     * Terminate a lease early
     */
    public function terminate(Request $request, Lease $lease)
    {
        try {
            DB::beginTransaction();

            // Use today as move-out date
            $moveOutDate = now();
            
            $lease->terminate($moveOutDate);

            // Add termination note
            $lease->notes = ($lease->notes ? $lease->notes . "\n\n" : '') 
                . "Terminated on {$moveOutDate->format('Y-m-d')}";
            $lease->save();

            // Update unit status
            $unit = Unit::find($lease->unit_id);
            if ($unit) {
                // Check if unit has any other active leases
                $hasOtherActiveLease = Lease::where('unit_id', $unit->id)
                    ->where('id', '!=', $lease->id)
                    ->whereIn('lease_status', ['active', 'pending'])
                    ->exists();
                
                if (!$hasOtherActiveLease) {
                    $unit->update(['status' => 'vacant']);
                }
            }

            DB::commit();

            return redirect()->route('leases.index')
                ->with('success', "Lease {$lease->lease_number} terminated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('leases.index')
                ->with('error', 'Failed to terminate lease: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified lease.
     */
    public function destroy(Lease $lease)
    {
        try {
            DB::beginTransaction();

            $unitId = $lease->unit_id;
            
            // Delete lease agreement file if exists
            if ($lease->lease_agreement_path) {
                Storage::disk('public')->delete($lease->lease_agreement_path);
            }
            
            // Delete lease
            $lease->delete();

            // Check if unit should be vacant
            $hasOtherActiveLease = Lease::where('unit_id', $unitId)
                ->whereIn('lease_status', ['active', 'pending'])
                ->exists();
            
            if (!$hasOtherActiveLease) {
                Unit::where('id', $unitId)->update(['status' => 'vacant']);
            }

            DB::commit();

            return redirect()->route('leases.index')
                ->with('success', 'Lease deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete lease: ' . $e->getMessage());
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
     * Export leases to CSV
     */
    public function export(Request $request)
    {
        $query = Lease::with(['tenant', 'unit.building']);
        
        if ($request->filled('status')) {
            $query->where('lease_status', $request->status);
        }
        
        if ($request->filled('building_id')) {
            $query->whereHas('unit', function($q) use ($request) {
                $q->where('building_id', $request->building_id);
            });
        }
        
        $leases = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'leases_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($leases) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Lease Number',
                'Tenant Name',
                'Unit',
                'Building',
                'Start Date',
                'End Date',
                'Monthly Rent',
                'Security Deposit',
                'Status',
                'Lease Type',
                'Days Remaining',
                'Created At'
            ]);
            
            // Data
            foreach ($leases as $lease) {
                fputcsv($file, [
                    $lease->lease_number,
                    $lease->tenant->full_name ?? 'N/A',
                    $lease->unit->unit_number ?? 'N/A',
                    $lease->unit->building->name ?? 'N/A',
                    $lease->start_date?->format('Y-m-d'),
                    $lease->end_date?->format('Y-m-d'),
                    $lease->monthly_rent,
                    $lease->security_deposit,
                    $lease->status_label,
                    $lease->lease_type ?? 'Standard',
                    $lease->days_remaining,
                    $lease->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Check tenant eligibility for new lease
     */
    public function checkTenantEligibility($tenantId)
    {
        $tenant = Tenant::with('currentLease')->find($tenantId);
        
        if (!$tenant) {
            return response()->json([
                'eligible' => false,
                'message' => 'Tenant not found'
            ], 404);
        }
        
        if ($tenant->currentLease) {
            return response()->json([
                'eligible' => false,
                'can_renew' => true,
                'message' => 'Tenant has active lease ending ' . $tenant->currentLease->end_date->format('M d, Y'),
                'current_lease' => [
                    'id' => $tenant->currentLease->id,
                    'end_date' => $tenant->currentLease->end_date->format('Y-m-d'),
                    'monthly_rent' => $tenant->currentLease->monthly_rent,
                    'status' => $tenant->currentLease->lease_status
                ]
            ]);
        }
        
        return response()->json([
            'eligible' => true,
            'can_renew' => false,
            'message' => 'Tenant is eligible for new lease'
        ]);
    }

    /**
     * Restore soft deleted lease
     */
    public function restore($id)
    {
        $lease = Lease::withTrashed()->findOrFail($id);
        
        try {
            $lease->restore();
            
            return redirect()->route('leases.show', $lease)
                ->with('success', 'Lease restored successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('leases.index')
                ->with('error', 'Failed to restore lease: ' . $e->getMessage());
        }
    }

    /**
     * Force delete lease
     */
    public function forceDelete($id)
    {
        $lease = Lease::withTrashed()->findOrFail($id);
        
        try {
            // Delete lease agreement file if exists
            if ($lease->lease_agreement_path) {
                Storage::disk('public')->delete($lease->lease_agreement_path);
            }
            
            $lease->forceDelete();
            
            return redirect()->route('leases.index')
                ->with('success', 'Lease permanently deleted!');
                
        } catch (\Exception $e) {
            return redirect()->route('leases.index')
                ->with('error', 'Failed to permanently delete lease: ' . $e->getMessage());
        }
    }
}