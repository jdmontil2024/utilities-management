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
        
        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->whereHas('currentLease', function($q) use ($request) {
                $q->where('lease_status', $request->status);
            });
        }
        
        // Filter by unit
        if ($request->has('unit_id') && $request->unit_id != '') {
            $query->where('unit_id', $request->unit_id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhereHas('building', function($b) use ($search) {
                      $b->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('unit', function($u) use ($search) {
                      $u->where('unit_number', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);
        
        $tenants = $query->paginate(20)->withQueryString();
        
        // Calculate tenant statistics using lease relationships
        if ($request->has('building_id') && $request->building_id != '') {
            $buildingId = $request->building_id;
            
            $tenantStats = [
                'total_active' => Tenant::where('building_id', $buildingId)
                    ->whereHas('currentLease', function($q) {
                        $q->where('lease_status', 'active');
                    })
                    ->count(),
                    
                'expiring_30_days' => Tenant::where('building_id', $buildingId)
                    ->whereHas('currentLease', function($q) {
                        $q->where('lease_status', 'active')
                          ->whereDate('end_date', '>=', now())
                          ->whereDate('end_date', '<=', now()->addDays(30));
                    })->count(),
                    
                'overdue' => Tenant::where('building_id', $buildingId)
                    ->whereHas('leases', function($q) {
                        $q->where('lease_status', 'expired')
                          ->orWhere(function($query) {
                              $query->where('lease_status', 'active')
                                    ->whereDate('end_date', '<', now());
                          });
                    })->count(),
                    
                'total_monthly_revenue' => Tenant::where('building_id', $buildingId)
                    ->whereHas('currentLease', function($q) {
                        $q->where('lease_status', 'active');
                    })
                    ->with('currentLease')
                    ->get()
                    ->sum(function($tenant) {
                        return $tenant->currentLease?->monthly_rent ?? 0;
                    }),
                    
                'total_pending' => Tenant::where('building_id', $buildingId)
                    ->whereHas('currentLease', function($q) {
                        $q->where('lease_status', 'pending');
                    })
                    ->count(),
            ];
        } else {
            $tenantStats = [
                'total_active' => Tenant::whereHas('currentLease', function($q) {
                    $q->where('lease_status', 'active');
                })->count(),
                
                'expiring_30_days' => Tenant::whereHas('currentLease', function($q) {
                    $q->where('lease_status', 'active')
                      ->whereDate('end_date', '>=', now())
                      ->whereDate('end_date', '<=', now()->addDays(30));
                })->count(),
                
                'overdue' => Tenant::whereHas('leases', function($q) {
                    $q->where('lease_status', 'expired')
                      ->orWhere(function($query) {
                          $query->where('lease_status', 'active')
                                ->whereDate('end_date', '<', now());
                      });
                })->count(),
                
                'total_monthly_revenue' => Tenant::whereHas('currentLease', function($q) {
                    $q->where('lease_status', 'active');
                })
                    ->with('currentLease')
                    ->get()
                    ->sum(function($tenant) {
                        return $tenant->currentLease?->monthly_rent ?? 0;
                    }),
                    
                'total_pending' => Tenant::whereHas('currentLease', function($q) {
                    $q->where('lease_status', 'pending');
                })->count(),
            ];
        }
        
        $buildings = Building::withCount('tenants')->orderBy('name')->get();
        $selectedBuilding = null;
        
        if ($request->has('building_id') && $request->building_id != '') {
            $selectedBuilding = Building::find($request->building_id);
        }
        
        return view('tenants.index', compact('tenants', 'tenantStats', 'buildings', 'selectedBuilding'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $buildings = Building::orderBy('name')->get();
        $units = collect(); // Empty collection initially
        
        // Check if building ID was passed in query string
        $selectedBuilding = null;
        $selectedUnit = null;
        
        if (request()->has('building_id')) {
            $selectedBuilding = Building::find(request('building_id'));
            if ($selectedBuilding) {
                $units = Unit::where('building_id', $selectedBuilding->id)
                            ->whereIn('status', ['vacant', 'ready'])
                            ->orderBy('unit_number')
                            ->get();
            }
        }
        
        if (request()->has('unit_id')) {
            $selectedUnit = Unit::with('building')->find(request('unit_id'));
            if ($selectedUnit) {
                $selectedBuilding = $selectedUnit->building;
                $units = Unit::where('building_id', $selectedBuilding->id)
                            ->whereIn('status', ['vacant', 'ready'])
                            ->orderBy('unit_number')
                            ->get();
            }
        }
        
        return view('tenants.create', compact('buildings', 'units', 'selectedBuilding', 'selectedUnit'));
    }

  /**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $validated = $request->validate([
        // Personal Information
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:tenants,email',
        'phone' => 'required|string|max:20',
        'alternate_phone' => 'nullable|string|max:20',
        'date_of_birth' => 'nullable|date',
        
        // Emergency Contact
        'emergency_contact_name' => 'nullable|string|max:255',
        'emergency_contact_phone' => 'nullable|string|max:20',
        'emergency_contact_relation' => 'nullable|string|max:100',
        
        // Identification
        'government_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        'id_type' => 'nullable|string|max:50',
        
        // Employment
        'occupation' => 'nullable|string|max:255',
        'employer' => 'nullable|string|max:255',
        'annual_income' => 'nullable|numeric|min:0',
        
        // Unit Assignment
        'building_id' => 'required|exists:buildings,id',
        'unit_id' => 'required|exists:units,id',
        
        // Lease Information
        'lease_start_date' => 'required|date',
        'lease_end_date' => 'required|date|after:lease_start_date',
        'monthly_rent' => 'required|numeric|min:0',
        'security_deposit' => 'nullable|numeric|min:0',
        'lease_status' => 'required|in:active,pending,expired,terminated',
        'lease_agreement' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        
        // Additional Occupants
        'number_of_occupants' => 'nullable|integer|min:1',
        'additional_occupants' => 'nullable|array',
        
        // Notes & Status
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ]);

    try {
        DB::beginTransaction();

        // Handle government ID upload
        if ($request->hasFile('government_id')) {
            $path = $request->file('government_id')->store('tenants/government_ids', 'public');
            $validated['government_id'] = $path;
        }

        // Handle lease agreement upload
        if ($request->hasFile('lease_agreement')) {
            $path = $request->file('lease_agreement')->store('tenants/lease_agreements', 'public');
            $validated['lease_agreement_path'] = $path;
        }

        // Set building_id from unit if not provided
        if (empty($validated['building_id']) && !empty($validated['unit_id'])) {
            $unit = Unit::find($validated['unit_id']);
            $validated['building_id'] = $unit->building_id;
        }

        // Set default values
        $validated['is_active'] = $request->has('is_active');
        $validated['number_of_occupants'] = $validated['number_of_occupants'] ?? 1;

        // Create the tenant
        $tenant = Tenant::create($validated);

        // GET UNIT AND BUILDING FOR LEASE NUMBER GENERATION
        $unit = Unit::with('building')->find($tenant->unit_id);
        
        if (!$unit) {
            throw new \Exception('Unit not found');
        }

        if (!$unit->building) {
            throw new \Exception('Building not found for this unit');
        }

        // CREATE LEASE RECORD WITH PROPER LEASE NUMBER
        $leaseData = [
            'tenant_id' => $tenant->id,
            'unit_id' => $tenant->unit_id,
            'start_date' => $tenant->lease_start_date,
            'end_date' => $tenant->lease_end_date,
            'move_in_date' => $tenant->lease_start_date,
            'monthly_rent' => $tenant->monthly_rent,
            'security_deposit' => $tenant->security_deposit ?? 0,
            'lease_status' => $tenant->lease_status,
            'lease_agreement_path' => $tenant->lease_agreement_path,
            'notes' => $tenant->notes,
            'lease_type' => 'Standard',
            'payment_due_day' => 1,
        ];

        // Generate lease number with proper parameters
        $leaseData['lease_number'] = Lease::generateLeaseNumber(
            $unit->building->name,
            $unit->building->id,
            $tenant->lease_start_date
        );

        // Create the lease
        $lease = Lease::create($leaseData);

        // IMPORTANT FIX: Update tenant's denormalized fields to match lease
        $tenant->update([
            'lease_start_date' => $lease->start_date,
            'lease_end_date' => $lease->end_date,
            'lease_status' => $lease->lease_status,
        ]);

        // If lease status is active, update unit status to occupied
        if ($lease->lease_status === 'active') {
            if ($unit && $unit->status !== 'occupied') {
                $unit->update(['status' => 'occupied']);
            }
        } elseif ($lease->lease_status === 'pending') {
            if ($unit && $unit->status !== 'reserved') {
                $unit->update(['status' => 'reserved']);
            }
        }

        DB::commit();

        Log::info('Tenant created successfully', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->full_name,
            'lease_status' => $lease->lease_status,
            'unit_id' => $unit->id,
            'unit_status' => $unit->fresh()->status
        ]);

        // Redirect back to filtered list if coming from building filter
        if ($request->has('from_building') || request()->has('building_id')) {
            return redirect()->route('tenants.index', ['building_id' => $tenant->building_id])
                ->with('success', 'Tenant created successfully! Status: ' . ucfirst($lease->lease_status));
        }

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant created successfully! Status: ' . ucfirst($lease->lease_status));

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Delete uploaded files if they exist
        if (isset($validated['government_id'])) {
            Storage::disk('public')->delete($validated['government_id']);
        }
        if (isset($validated['lease_agreement_path'])) {
            Storage::disk('public')->delete($validated['lease_agreement_path']);
        }
        
        Log::error('Failed to create tenant: ' . $e->getMessage(), [
            'error' => $e,
            'request' => $request->all()
        ]);
        
        return back()->withInput()->with('error', 'Failed to create tenant: ' . $e->getMessage());
    }
}

    /**
     * Display the specified resource.
     * FIXED: Load maintenance requests for ALL units the tenant has ever occupied
     */
    public function show(Tenant $tenant)
    {
        // Load all necessary relationships
        $tenant->load([
            'building',
            'unit',
            'currentLease',
            'leases' => function($query) {
                $query->with('unit')->latest()->limit(10);
            },
            'paymentMethods'
        ]);
        
        // FIXED: Get maintenance requests for ALL units this tenant has ever occupied
        $maintenanceRequests = collect();
        
        // Get all unit IDs this tenant has ever occupied through leases
        $unitIds = $tenant->leases()->pluck('unit_id')->unique()->toArray();
        
        // Also include current unit if not already in the list
        if ($tenant->unit_id && !in_array($tenant->unit_id, $unitIds)) {
            $unitIds[] = $tenant->unit_id;
        }
        
        if (!empty($unitIds)) {
            $maintenanceRequests = \App\Models\MaintenanceRequest::whereIn('unit_id', $unitIds)
                ->with(['maintenanceCategory', 'assignedVendor', 'unit', 'unit.building'])
                ->latest()
                ->limit(20)
                ->get();
        }

        return view('tenants.show', compact('tenant', 'maintenanceRequests'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        // Load relationships
        $tenant->load(['building', 'unit', 'currentLease']);
        
        // Get ALL buildings for dropdown (not just the current one)
        $buildings = Building::orderBy('name')->get();
        
        // Get units from the current building (will be replaced via AJAX when building changes)
        $units = collect();
        if ($tenant->building_id) {
            $units = Unit::where('building_id', $tenant->building_id)
                        ->orderBy('unit_number')
                        ->get();
        }
        
        // Format building options for better display
        $buildingOptions = $buildings->map(function($building) {
            $availableUnits = $building->units()->whereIn('status', ['vacant', 'ready'])->count();
            return [
                'id' => $building->id,
                'name' => $building->name,
                'display_name' => $building->name . ' - ' . ($building->address ?? 'No address') . ' (' . $availableUnits . ' available)',
                'available_units' => $availableUnits
            ];
        });
        
        // Lease status options
        $leaseStatusOptions = [
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'terminated' => 'Terminated'
        ];
        
        // ID Type options
        $idTypeOptions = [
            'passport' => 'Passport',
            'drivers_license' => "Driver's License",
            'national_id' => 'National ID',
            'postal_id' => 'Postal ID',
            'senior_citizen' => 'Senior Citizen ID',
            'umid' => 'UMID',
            'prc_id' => 'PRC ID',
            'voters_id' => "Voter's ID",
            'other' => 'Other'
        ];
        
        return view('tenants.edit', compact(
            'tenant', 
            'buildings', 
            'buildingOptions',
            'units', 
            'leaseStatusOptions', 
            'idTypeOptions'
        ));
    }

    /**
     * Update the specified resource in storage.
     * FIXED: Properly update lease status when changed
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email,' . $tenant->id,
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            
            // Emergency Contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            
            // Identification
            'government_id' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'id_type' => 'nullable|string|max:50',
            
            // Employment
            'occupation' => 'nullable|string|max:255',
            'employer' => 'nullable|string|max:255',
            'annual_income' => 'nullable|numeric|min:0',
            
            // Unit Assignment - CRITICAL FOR REASSIGNMENT
            'building_id' => 'required|exists:buildings,id',
            'unit_id' => 'required|exists:units,id',
            
            // Lease Information
            'lease_start_date' => 'required|date',
            'lease_end_date' => 'required|date|after:lease_start_date',
            'monthly_rent' => 'required|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'lease_status' => 'required|in:active,pending,expired,terminated',
            'lease_agreement' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            
            // Additional Occupants
            'number_of_occupants' => 'nullable|integer|min:1',
            'additional_occupants' => 'nullable|array',
            
            // Notes & Status
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Store old values for comparison
            $oldUnitId = $tenant->unit_id;
            $oldLeaseStatus = $tenant->lease_status;
            $oldLeaseStartDate = $tenant->lease_start_date;
            $oldLeaseEndDate = $tenant->lease_end_date;

            // Handle government ID upload
            if ($request->hasFile('government_id')) {
                // Delete old file
                if ($tenant->government_id) {
                    Storage::disk('public')->delete($tenant->government_id);
                }
                $path = $request->file('government_id')->store('tenants/government_ids', 'public');
                $validated['government_id'] = $path;
            }

            // Handle lease agreement upload
            if ($request->hasFile('lease_agreement')) {
                // Delete old file
                if ($tenant->lease_agreement_path) {
                    Storage::disk('public')->delete($tenant->lease_agreement_path);
                }
                $path = $request->file('lease_agreement')->store('tenants/lease_agreements', 'public');
                $validated['lease_agreement_path'] = $path;
            }

            // CRITICAL: Set building_id from the selected unit
            if (!empty($validated['unit_id'])) {
                $unit = Unit::find($validated['unit_id']);
                if ($unit) {
                    $validated['building_id'] = $unit->building_id;
                }
            }

            // Set boolean
            $validated['is_active'] = $request->has('is_active');

            // ====================================================================
            // FIXED: Handle Lease Status Updates Properly
            // ====================================================================
            
            // Case 1: Tenant is moving to a DIFFERENT unit
            if ($oldUnitId != $validated['unit_id']) {
                
                // 1. TERMINATE the OLD active lease (if exists)
                if ($tenant->currentLease) {
                    // Calculate the move-out date (day before new lease starts)
                    $moveOutDate = date('Y-m-d', strtotime($validated['lease_start_date'] . ' -1 day'));
                    
                    // Get the old unit for note
                    $oldUnit = Unit::find($oldUnitId);
                    $newUnit = Unit::find($validated['unit_id']);
                    
                    // Update the old lease with termination details
                    $tenant->currentLease->update([
                        'end_date' => $moveOutDate,
                        'move_out_date' => $moveOutDate,
                        'lease_status' => 'terminated',
                        'notes' => ($tenant->currentLease->notes ? $tenant->currentLease->notes . "\n\n" : '') 
                            . "Terminated on {$moveOutDate} due to transfer to Unit " . ($newUnit->unit_number ?? 'new unit')
                    ]);
                    
                    // Log the termination
                    Log::info('Lease terminated due to unit transfer', [
                        'lease_id' => $tenant->currentLease->id,
                        'old_unit' => $oldUnitId,
                        'new_unit' => $validated['unit_id'],
                        'move_out_date' => $moveOutDate
                    ]);
                }
                
                // 2. Also check if there's ANY other active lease for this tenant
                $anyActiveLease = Lease::where('tenant_id', $tenant->id)
                    ->where('lease_status', 'active')
                    ->where('id', '!=', $tenant->currentLease?->id)
                    ->first();
                    
                if ($anyActiveLease) {
                    $moveOutDate = date('Y-m-d', strtotime($validated['lease_start_date'] . ' -1 day'));
                    $anyActiveLease->update([
                        'end_date' => $moveOutDate,
                        'move_out_date' => $moveOutDate,
                        'lease_status' => 'terminated',
                        'notes' => ($anyActiveLease->notes ? $anyActiveLease->notes . "\n\n" : '') 
                            . "Terminated on {$moveOutDate} due to transfer"
                    ]);
                }
                
                // 3. CREATE a NEW lease for the new unit with the status from form
                $newUnit = Unit::with('building')->find($validated['unit_id']);
                
                if (!$newUnit) {
                    throw new \Exception('New unit not found');
                }
                
                // Generate new lease number
                $leaseNumber = Lease::generateLeaseNumber(
                    $newUnit->building->name,
                    $newUnit->building->id,
                    $validated['lease_start_date']
                );
                
                // Create the new lease with the CORRECT status from form
                $newLease = Lease::create([
                    'tenant_id' => $tenant->id,
                    'unit_id' => $validated['unit_id'],
                    'lease_number' => $leaseNumber,
                    'start_date' => $validated['lease_start_date'],
                    'end_date' => $validated['lease_end_date'],
                    'move_in_date' => $validated['lease_start_date'],
                    'monthly_rent' => $validated['monthly_rent'],
                    'security_deposit' => $validated['security_deposit'] ?? 0,
                    'payment_due_day' => 1,
                    'lease_status' => $validated['lease_status'], // Use status from form
                    'lease_type' => 'Standard',
                    'lease_agreement_path' => $validated['lease_agreement_path'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
                
                // Update the tenant's denormalized fields to match the NEW lease
                $validated['lease_start_date'] = $newLease->start_date;
                $validated['lease_end_date'] = $newLease->end_date;
                $validated['lease_status'] = $newLease->lease_status;
                
                // Handle unit status changes
                // Old unit - set to vacant if no other active tenants
                if ($oldUnitId) {
                    $oldUnit = Unit::find($oldUnitId);
                    if ($oldUnit) {
                        $hasOtherActiveTenant = Tenant::where('unit_id', $oldUnitId)
                            ->where('id', '!=', $tenant->id)
                            ->whereHas('currentLease', function($q) {
                                $q->whereIn('lease_status', ['active', 'pending']);
                            })
                            ->exists();
                        
                        if (!$hasOtherActiveTenant) {
                            $oldUnit->update(['status' => 'vacant']);
                        }
                    }
                }
                
                // New unit - set status based on lease status
                if ($validated['lease_status'] === 'active') {
                    $newUnit->update(['status' => 'occupied']);
                } elseif ($validated['lease_status'] === 'pending') {
                    $newUnit->update(['status' => 'reserved']);
                }
            } 
            // Case 2: Same unit, but lease details changed
            else {
                // Get the current active lease
                $currentLease = $tenant->currentLease;
                
                if ($currentLease) {
                    // FIXED: Update the existing lease with ALL fields including status
                    $currentLease->update([
                        'start_date' => $validated['lease_start_date'],
                        'end_date' => $validated['lease_end_date'],
                        'monthly_rent' => $validated['monthly_rent'],
                        'security_deposit' => $validated['security_deposit'] ?? 0,
                        'lease_status' => $validated['lease_status'], // IMPORTANT: Update status
                        'lease_agreement_path' => $validated['lease_agreement_path'] ?? $currentLease->lease_agreement_path,
                        'notes' => $validated['notes'] ?? $currentLease->notes,
                    ]);
                    
                    Log::info('Lease status updated', [
                        'lease_id' => $currentLease->id,
                        'old_status' => $oldLeaseStatus,
                        'new_status' => $validated['lease_status']
                    ]);
                } else {
                    // Create new lease if none exists
                    $unit = Unit::with('building')->find($validated['unit_id']);
                    
                    $leaseData = [
                        'tenant_id' => $tenant->id,
                        'unit_id' => $validated['unit_id'],
                        'start_date' => $validated['lease_start_date'],
                        'end_date' => $validated['lease_end_date'],
                        'move_in_date' => $validated['lease_start_date'],
                        'monthly_rent' => $validated['monthly_rent'],
                        'security_deposit' => $validated['security_deposit'] ?? 0,
                        'lease_status' => $validated['lease_status'],
                        'lease_agreement_path' => $validated['lease_agreement_path'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                        'lease_type' => 'Standard',
                        'payment_due_day' => 1,
                    ];

                    if ($unit && $unit->building) {
                        $leaseData['lease_number'] = Lease::generateLeaseNumber(
                            $unit->building->name,
                            $unit->building->id,
                            $validated['lease_start_date']
                        );
                    }

                    Lease::create($leaseData);
                }
                
                // Update unit status based on lease status
                $unit = Unit::find($validated['unit_id']);
                if ($unit) {
                    if ($validated['lease_status'] === 'active') {
                        $unit->update(['status' => 'occupied']);
                    } elseif ($validated['lease_status'] === 'pending') {
                        $unit->update(['status' => 'reserved']);
                    } else {
                        // Check if unit has any other active/pending tenants
                        $hasOtherActiveTenant = Tenant::where('unit_id', $unit->id)
                            ->where('id', '!=', $tenant->id)
                            ->whereHas('currentLease', function($q) {
                                $q->whereIn('lease_status', ['active', 'pending']);
                            })
                            ->exists();
                        
                        if (!$hasOtherActiveTenant) {
                            $unit->update(['status' => 'vacant']);
                        }
                    }
                }
            }

            // Update the tenant with the validated data
            $tenant->update($validated);

            DB::commit();

            // Redirect with success message showing the new status
            $statusMessage = 'Tenant updated successfully! Status: ' . ucfirst($validated['lease_status']);
            
            if ($request->has('redirect_to_building') || request()->has('building_id')) {
                $buildingId = $request->input('building_id', $tenant->building_id);
                return redirect()->route('buildings.show', ['building' => $buildingId, '#tenants'])
                    ->with('success', $statusMessage);
            }

            if ($request->has('return_to_index')) {
                return redirect()->route('tenants.index', ['building_id' => $tenant->building_id])
                    ->with('success', $statusMessage);
            }

            return redirect()->route('tenants.show', $tenant)
                ->with('success', $statusMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update tenant: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'error' => $e
            ]);
            return back()->withInput()->with('error', 'Failed to update tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        try {
            DB::beginTransaction();

            // Store building_id for redirect
            $buildingId = $tenant->building_id;

            // Check if tenant has active lease
            if ($tenant->currentLease) {
                // Free up the unit
                $unit = Unit::find($tenant->unit_id);
                if ($unit) {
                    // Check if unit has other active tenants
                    $hasOtherActiveTenant = Tenant::where('unit_id', $unit->id)
                        ->where('id', '!=', $tenant->id)
                        ->whereHas('currentLease')
                        ->exists();
                    
                    if (!$hasOtherActiveTenant) {
                        $unit->update(['status' => 'vacant']);
                    }
                }
            }

            // Delete files
            if ($tenant->government_id) {
                Storage::disk('public')->delete($tenant->government_id);
            }
            if ($tenant->lease_agreement_path) {
                Storage::disk('public')->delete($tenant->lease_agreement_path);
            }

            // Delete the tenant (this will soft delete all related leases due to cascade)
            $tenant->delete();

            DB::commit();

            // Check if we should redirect back to filtered index
            if (request()->has('return_to_index')) {
                return redirect()->route('tenants.index', ['building_id' => $buildingId])
                    ->with('success', 'Tenant deleted successfully!');
            }

            return redirect()->route('tenants.index')
                ->with('success', 'Tenant deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tenants.show', $tenant)
                ->with('error', 'Failed to delete tenant: ' . $e->getMessage());
        }
    }

    /**
     * Get units by building (AJAX) - FIXED to include proper status and tenant info
     */
    public function getUnitsByBuilding($buildingId)
    {
        $units = Unit::where('building_id', $buildingId)
                    ->orderBy('unit_number')
                    ->get([
                        'id', 
                        'unit_number', 
                        'bedrooms', 
                        'bathrooms', 
                        'monthly_rent', 
                        'floor', 
                        'area', 
                        'status',
                        'unit_type'
                    ]);
        
        // Add a flag for each unit indicating if it has an active tenant
        $unitsWithTenantInfo = $units->map(function($unit) {
            $hasActiveTenant = Tenant::where('unit_id', $unit->id)
                ->whereHas('currentLease', function($q) {
                    $q->whereIn('lease_status', ['active', 'pending']);
                })
                ->exists();
                
            $activeTenant = Tenant::where('unit_id', $unit->id)
                ->whereHas('currentLease', function($q) {
                    $q->whereIn('lease_status', ['active', 'pending']);
                })
                ->with('currentLease')
                ->first();
                
            return [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'bedrooms' => $unit->bedrooms,
                'bathrooms' => $unit->bathrooms,
                'monthly_rent' => $unit->monthly_rent,
                'floor' => $unit->floor,
                'area' => $unit->area,
                'status' => $unit->status,
                'unit_type' => $unit->unit_type,
                'has_active_tenant' => $hasActiveTenant,
                'current_tenant_name' => $activeTenant?->full_name,
                'lease_status' => $activeTenant?->currentLease?->lease_status,
                'is_available' => in_array($unit->status, ['vacant', 'ready']) && !$hasActiveTenant
            ];
        });
        
        return response()->json($unitsWithTenantInfo);
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

        $unit = Unit::with('building')->find($request->unit_id);
        
        // Check if unit has any active tenant (including pending)
        $activeTenant = Tenant::where('unit_id', $request->unit_id)
            ->whereHas('currentLease', function($q) {
                $q->whereIn('lease_status', ['active', 'pending']);
            })
            ->with('currentLease')
            ->first();

        if ($activeTenant) {
            return response()->json([
                'has_active_tenant' => true,
                'tenant_name' => $activeTenant->full_name,
                'lease_status' => $activeTenant->currentLease?->lease_status,
                'lease_end_date' => $activeTenant->currentLease?->end_date?->format('M d, Y'),
                'status' => $unit->status
            ]);
        }

        return response()->json([
            'has_active_tenant' => false,
            'status' => $unit->status
        ]);
    }

    /**
     * Export tenants to CSV
     */
    public function export(Request $request)
    {
        $query = Tenant::with(['building', 'unit', 'currentLease']);
        
        // Filter by building if specified
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        
        $tenants = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'tenants_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($tenants) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($file, [
                'Tenant Name',
                'Email',
                'Phone',
                'Building',
                'Unit',
                'Lease Start',
                'Lease End',
                'Monthly Rent',
                'Status',
                'Tenant Since'
            ]);
            
            // Data
            foreach ($tenants as $tenant) {
                fputcsv($file, [
                    $tenant->full_name,
                    $tenant->email,
                    $tenant->phone,
                    $tenant->building->name ?? 'N/A',
                    $tenant->unit->unit_number ?? 'N/A',
                    $tenant->currentLease?->start_date?->format('Y-m-d'),
                    $tenant->currentLease?->end_date?->format('Y-m-d'),
                    $tenant->currentLease?->monthly_rent ?? 0,
                    $tenant->currentLease?->lease_status_label ?? 'No Lease',
                    $tenant->created_at->format('Y-m-d'),
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
        
        try {
            $tenant->restore();
            
            return redirect()->route('tenants.show', $tenant)
                ->with('success', 'Tenant restored successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('tenants.index')
                ->with('error', 'Failed to restore tenant: ' . $e->getMessage());
        }
    }

    /**
     * Force delete tenant
     */
    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        
        try {
            // Delete files
            if ($tenant->government_id) {
                Storage::disk('public')->delete($tenant->government_id);
            }
            if ($tenant->lease_agreement_path) {
                Storage::disk('public')->delete($tenant->lease_agreement_path);
            }
            
            $tenant->forceDelete();
            
            return redirect()->route('tenants.index')
                ->with('success', 'Tenant permanently deleted!');
                
        } catch (\Exception $e) {
            return redirect()->route('tenants.index')
                ->with('error', 'Failed to permanently delete tenant: ' . $e->getMessage());
        }
    }
}