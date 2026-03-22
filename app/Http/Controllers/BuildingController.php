<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Unit;
use App\Models\Tenant;
use App\Models\Lease;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Building::query();
        
        // Filter by building type
        if ($request->has('building_type') && $request->building_type != '') {
            $query->where('building_type', $request->building_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        // Get buildings with counts
        $buildings = $query->withCount([
            'units as units_count',
            'units as occupied_units_count' => function($q) {
                $q->whereHas('leases', function($q2) {
                    $q2->where('lease_status', 'active');
                });
            }
        ])
        ->latest()
        ->paginate(10)
        ->withQueryString();
        
        // Add active tenants count manually
        foreach ($buildings as $building) {
            $building->active_tenants_count = Lease::whereHas('unit', function($q) use ($building) {
                    $q->where('building_id', $building->id);
                })
                ->where('lease_status', 'active')
                ->distinct('tenant_id')
                ->count('tenant_id');
        }
        
        // Get building type counts for filter display
        $typeCounts = [
            'residential' => Building::where('building_type', 'residential')->count(),
            'commercial' => Building::where('building_type', 'commercial')->count(),
            'mixed' => Building::where('building_type', 'mixed')->count(),
        ];
        
        return view('buildings.index', compact('buildings', 'typeCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('buildings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
            'total_floors' => 'required|integer|min:1',
            'total_units' => 'required|integer|min:0',
            'year_built' => 'required|integer|min:1800|max:' . date('Y'),
            'building_type' => 'required|in:residential,commercial,mixed',
            'has_elevator' => 'boolean',
            'has_parking' => 'boolean',
            'total_area' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,under_construction,renovation',
        ]);

        // Set default value for country if empty
        if (empty($validated['country'])) {
            $validated['country'] = 'United States';
        }

        // Set default values for checkboxes
        $validated['has_elevator'] = $request->has('has_elevator');
        $validated['has_parking'] = $request->has('has_parking');
        
        $building = Building::create($validated);
        
        return redirect()->route('buildings.index')
            ->with('success', 'Building added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        // Load all necessary relationships
        $building->load([
            'units' => function($query) {
                $query->orderBy('unit_number');
            },
            'floorPlans',
            'photos'
        ]);

        // Get units with their current leases and tenants
        $units = Unit::where('building_id', $building->id)
            ->with(['leases' => function($q) {
                $q->whereIn('lease_status', ['active', 'pending'])
                  ->with('tenant');
            }])
            ->orderBy('unit_number')
            ->get();

        // Process each unit to get current tenant
        foreach ($units as $unit) {
            $currentLease = $unit->leases->first();
            $unit->currentTenant = $currentLease?->tenant;
            $unit->currentLease = $currentLease;
        }
        
        $building->units = $units;

        // Get tenants with active or pending leases
        $currentTenants = Tenant::whereHas('leases', function($query) use ($building) {
                $query->whereHas('unit', function($q) use ($building) {
                    $q->where('building_id', $building->id);
                })
                ->whereIn('lease_status', ['active', 'pending']);
            })
            ->with(['leases' => function($q) {
                $q->whereIn('lease_status', ['active', 'pending'])
                  ->with('unit');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get maintenance requests
        $maintenanceRequests = $building->maintenanceRequests()
            ->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor'])
            ->latest()
            ->take(20)
            ->get();

        // Calculate accurate stats
        $totalUnits = $units->count();
        
        $occupiedUnits = $units->filter(function($unit) {
            return $unit->currentLease && $unit->currentLease->lease_status === 'active';
        })->count();
        
        $pendingUnits = $units->filter(function($unit) {
            return $unit->currentLease && $unit->currentLease->lease_status === 'pending';
        })->count();
        
        $vacantUnits = $totalUnits - $occupiedUnits - $pendingUnits;
        
        $monthlyRevenue = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->whereIn('lease_status', ['active', 'pending'])
            ->sum('monthly_rent');

        $averageRent = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->whereIn('lease_status', ['active', 'pending'])
            ->avg('monthly_rent');

        // Add calculated attributes for view
        $building->units_count = $totalUnits;
        $building->occupied_units_count = $occupiedUnits;
        $building->vacant_units_count = $vacantUnits;
        $building->pending_units_count = $pendingUnits;
        $building->active_tenants_count = $currentTenants->count();
        $building->monthly_revenue = $monthlyRevenue;
        $building->average_rent = $averageRent;
        $building->occupancy_rate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;
        
        return view('buildings.show', compact(
            'building', 
            'currentTenants', 
            'maintenanceRequests',
            'totalUnits',
            'occupiedUnits',
            'pendingUnits',
            'vacantUnits',
            'monthlyRevenue',
            'averageRent'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        return view('buildings.edit', compact('building'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Building $building)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
            'total_floors' => 'required|integer|min:1',
            'total_units' => 'required|integer|min:0',
            'year_built' => 'required|integer|min:1800|max:' . date('Y'),
            'building_type' => 'required|in:residential,commercial,mixed',
            'has_elevator' => 'boolean',
            'has_parking' => 'boolean',
            'total_area' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,under_construction,renovation',
        ]);

        // Set default values for checkboxes
        $validated['has_elevator'] = $request->has('has_elevator');
        $validated['has_parking'] = $request->has('has_parking');
        
        $building->update($validated);
        
        return redirect()->route('buildings.show', $building)
            ->with('success', 'Building updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        // Check if building has units before deleting
        if ($building->units()->count() > 0) {
            return redirect()->route('buildings.index')
                ->with('error', 'Cannot delete building with units. Remove all units first.');
        }
        
        $building->delete();
        
        return redirect()->route('buildings.index')
            ->with('success', 'Building deleted successfully!');
    }

    /**
     * Restore the specified soft deleted resource.
     */
    public function restore($id)
    {
        $building = Building::withTrashed()->findOrFail($id);
        $building->restore();
        
        return redirect()->route('buildings.index')
            ->with('success', 'Building restored successfully!');
    }

    /**
     * Get building statistics (for AJAX requests).
     */
    public function statistics(Building $building)
    {
        $totalUnits = $building->units()->count();
        
        $occupiedUnits = Unit::where('building_id', $building->id)
            ->whereHas('leases', function($q) {
                $q->where('lease_status', 'active');
            })
            ->count();
        
        $pendingUnits = Unit::where('building_id', $building->id)
            ->whereHas('leases', function($q) {
                $q->where('lease_status', 'pending');
            })
            ->count();
        
        $vacantUnits = $totalUnits - $occupiedUnits - $pendingUnits;
        
        $activeTenants = Tenant::whereHas('leases', function($q) use ($building) {
                $q->whereHas('unit', function($q2) use ($building) {
                    $q2->where('building_id', $building->id);
                })
                ->where('lease_status', 'active');
            })
            ->count();
        
        $pendingTenants = Tenant::whereHas('leases', function($q) use ($building) {
                $q->whereHas('unit', function($q2) use ($building) {
                    $q2->where('building_id', $building->id);
                })
                ->where('lease_status', 'pending');
            })
            ->count();

        $monthlyRevenue = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->whereIn('lease_status', ['active', 'pending'])
            ->sum('monthly_rent');

        $averageRent = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->whereIn('lease_status', ['active', 'pending'])
            ->avg('monthly_rent');

        $expiringLeases = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();

        $stats = [
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $vacantUnits,
            'pending_units' => $pendingUnits,
            'total_floors' => $building->total_floors,
            'active_tenants' => $activeTenants,
            'pending_tenants' => $pendingTenants,
            'open_maintenance' => $building->maintenanceRequests()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0,
            'monthly_revenue' => $monthlyRevenue,
            'formatted_monthly_revenue' => '₱' . number_format($monthlyRevenue, 0),
            'average_rent' => $averageRent ?? 0,
            'formatted_average_rent' => '₱' . number_format($averageRent ?? 0, 0),
            'expiring_leases' => $expiringLeases,
        ];

        // Return JSON for AJAX requests, HTML for regular requests
        if (request()->expectsJson()) {
            return response()->json($stats);
        }
        
        return view('buildings.statistics', compact('building', 'stats'));
    }

    /**
     * Get current tenants for the building (for AJAX requests).
     */
    public function getCurrentTenants(Building $building)
    {
        $tenants = Tenant::whereHas('leases', function($q) use ($building) {
                $q->whereHas('unit', function($q2) use ($building) {
                    $q2->where('building_id', $building->id);
                })
                ->whereIn('lease_status', ['active', 'pending']);
            })
            ->with(['leases' => function($q) {
                $q->whereIn('lease_status', ['active', 'pending'])
                  ->with('unit');
            }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($tenant) {
                $currentLease = $tenant->leases->first();
                $daysRemaining = $currentLease && $currentLease->end_date 
                    ? now()->diffInDays($currentLease->end_date, false) 
                    : null;
                
                return [
                    'id' => $tenant->id,
                    'full_name' => $tenant->first_name . ' ' . $tenant->last_name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'unit_number' => $currentLease?->unit?->unit_number ?? 'N/A',
                    'lease_start_date' => $currentLease?->start_date?->format('M d, Y'),
                    'lease_end_date' => $currentLease?->end_date?->format('M d, Y'),
                    'monthly_rent' => $currentLease?->monthly_rent ?? 0,
                    'formatted_monthly_rent' => '₱' . number_format($currentLease?->monthly_rent ?? 0, 0),
                    'lease_status' => $currentLease?->lease_status,
                    'is_lease_expiring_soon' => $daysRemaining !== null && $daysRemaining <= 30 && $daysRemaining > 0,
                    'days_remaining' => $daysRemaining,
                ];
            });
        
        return response()->json([
            'success' => true,
            'tenants' => $tenants,
            'total' => $tenants->count()
        ]);
    }

    /**
     * Get units with their current tenants (for AJAX requests).
     */
    public function getUnitsWithTenants(Building $building)
    {
        $units = Unit::where('building_id', $building->id)
            ->with(['leases' => function($q) {
                $q->whereIn('lease_status', ['active', 'pending'])
                  ->with('tenant');
            }])
            ->orderBy('unit_number')
            ->get()
            ->map(function($unit) {
                $currentLease = $unit->leases->first();
                $isOccupied = $currentLease && $currentLease->lease_status === 'active';
                $isPending = $currentLease && $currentLease->lease_status === 'pending';
                
                return [
                    'id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'unit_type' => ucfirst($unit->unit_type ?? 'Standard'),
                    'status' => $unit->status,
                    'monthly_rent' => $unit->monthly_rent,
                    'formatted_monthly_rent' => '₱' . number_format($unit->monthly_rent, 0),
                    'current_tenant' => $currentLease?->tenant ? [
                        'id' => $currentLease->tenant->id,
                        'full_name' => $currentLease->tenant->first_name . ' ' . $currentLease->tenant->last_name,
                        'email' => $currentLease->tenant->email,
                        'phone' => $currentLease->tenant->phone,
                        'lease_status' => $currentLease->lease_status,
                        'lease_end_date' => $currentLease->end_date?->format('M d, Y'),
                    ] : null,
                    'is_occupied' => $isOccupied,
                    'is_pending' => $isPending,
                ];
            });
        
        $occupied = $units->where('is_occupied', true)->count();
        $pending = $units->where('is_pending', true)->count();
        
        return response()->json([
            'success' => true,
            'units' => $units,
            'total' => $units->count(),
            'occupied' => $occupied,
            'pending' => $pending,
            'vacant' => $units->count() - $occupied - $pending
        ]);
    }

    /**
     * Refresh building statistics.
     */
    public function refreshStatistics(Building $building)
    {
        $totalUnits = $building->units()->count();
        
        $occupiedUnits = Unit::where('building_id', $building->id)
            ->whereHas('leases', function($q) {
                $q->where('lease_status', 'active');
            })
            ->count();
        
        $pendingUnits = Unit::where('building_id', $building->id)
            ->whereHas('leases', function($q) {
                $q->where('lease_status', 'pending');
            })
            ->count();
        
        $activeTenants = Tenant::whereHas('leases', function($q) use ($building) {
                $q->whereHas('unit', function($q2) use ($building) {
                    $q2->where('building_id', $building->id);
                })
                ->where('lease_status', 'active');
            })
            ->count();
        
        $pendingTenants = Tenant::whereHas('leases', function($q) use ($building) {
                $q->whereHas('unit', function($q2) use ($building) {
                    $q2->where('building_id', $building->id);
                })
                ->where('lease_status', 'pending');
            })
            ->count();
        
        $monthlyRevenue = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->whereIn('lease_status', ['active', 'pending'])
            ->sum('monthly_rent');
        
        $expiringLeases = Lease::whereHas('unit', function($q) use ($building) {
                $q->where('building_id', $building->id);
            })
            ->where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(30))
            ->count();
        
        return response()->json([
            'success' => true,
            'message' => 'Building statistics refreshed',
            'stats' => [
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'pending_units' => $pendingUnits,
                'vacant_units' => $totalUnits - $occupiedUnits - $pendingUnits,
                'active_tenants' => $activeTenants,
                'pending_tenants' => $pendingTenants,
                'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0,
                'monthly_revenue' => $monthlyRevenue,
                'formatted_monthly_revenue' => '₱' . number_format($monthlyRevenue, 0),
                'expiring_leases' => $expiringLeases,
            ]
        ]);
    }

    /**
     * Export building data to CSV.
     */
    public function export(Building $building)
    {
        $filename = 'building_' . $building->id . '_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($building) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Building Information
            fputcsv($file, ['BUILDING INFORMATION']);
            fputcsv($file, ['Name', $building->name]);
            fputcsv($file, ['Address', $building->address . ', ' . $building->city . ', ' . $building->state . ' ' . $building->zip_code]);
            fputcsv($file, ['Type', ucfirst($building->building_type ?? 'N/A')]);
            fputcsv($file, ['Status', ucfirst(str_replace('_', ' ', $building->status))]);
            fputcsv($file, ['Total Units', $building->units()->count()]);
            
            $activeTenants = Tenant::whereHas('leases', function($q) use ($building) {
                    $q->whereHas('unit', function($q2) use ($building) {
                        $q2->where('building_id', $building->id);
                    })
                    ->where('lease_status', 'active');
                })
                ->count();
            
            $pendingTenants = Tenant::whereHas('leases', function($q) use ($building) {
                    $q->whereHas('unit', function($q2) use ($building) {
                        $q2->where('building_id', $building->id);
                    })
                    ->where('lease_status', 'pending');
                })
                ->count();
            
            $monthlyRevenue = Lease::whereHas('unit', function($q) use ($building) {
                    $q->where('building_id', $building->id);
                })
                ->whereIn('lease_status', ['active', 'pending'])
                ->sum('monthly_rent');
            
            fputcsv($file, ['Active Tenants', $activeTenants]);
            fputcsv($file, ['Pending Tenants', $pendingTenants]);
            fputcsv($file, ['Monthly Revenue', '₱' . number_format($monthlyRevenue, 2)]);
            fputcsv($file, ['']);
            
            // Current Tenants
            fputcsv($file, ['CURRENT TENANTS']);
            fputcsv($file, ['Tenant Name', 'Unit', 'Email', 'Phone', 'Lease Start', 'Lease End', 'Monthly Rent', 'Status']);
            
            $currentTenants = Tenant::whereHas('leases', function($q) use ($building) {
                    $q->whereHas('unit', function($q2) use ($building) {
                        $q2->where('building_id', $building->id);
                    })
                    ->whereIn('lease_status', ['active', 'pending']);
                })
                ->with(['leases' => function($q) {
                    $q->whereIn('lease_status', ['active', 'pending'])
                      ->with('unit');
                }])
                ->get();
            
            foreach ($currentTenants as $tenant) {
                $currentLease = $tenant->leases->first();
                fputcsv($file, [
                    $tenant->first_name . ' ' . $tenant->last_name,
                    $currentLease?->unit?->unit_number ?? 'N/A',
                    $tenant->email,
                    $tenant->phone,
                    $currentLease?->start_date?->format('Y-m-d'),
                    $currentLease?->end_date?->format('Y-m-d'),
                    $currentLease?->monthly_rent ?? 0,
                    ucfirst($currentLease?->lease_status ?? 'No Lease'),
                ]);
            }
            
            fputcsv($file, ['']);
            
            // Units
            fputcsv($file, ['UNITS']);
            fputcsv($file, ['Unit #', 'Type', 'Floor', 'Status', 'Monthly Rent', 'Current Tenant', 'Lease Status', 'Lease End']);
            
            $units = Unit::where('building_id', $building->id)
                ->with(['leases' => function($q) {
                    $q->whereIn('lease_status', ['active', 'pending'])
                      ->with('tenant');
                }])
                ->orderBy('unit_number')
                ->get();
            
            foreach ($units as $unit) {
                $currentLease = $unit->leases->first();
                $leaseStatus = $currentLease?->lease_status;
                $statusDisplay = match($leaseStatus) {
                    'active' => 'Active',
                    'pending' => 'Pending',
                    default => 'Vacant'
                };
                
                fputcsv($file, [
                    $unit->unit_number,
                    ucfirst($unit->unit_type ?? 'Standard'),
                    $unit->floor ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $unit->status)),
                    $unit->monthly_rent,
                    $currentLease?->tenant ? $currentLease->tenant->first_name . ' ' . $currentLease->tenant->last_name : 'Vacant',
                    $statusDisplay,
                    $currentLease?->end_date?->format('Y-m-d') ?? '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}