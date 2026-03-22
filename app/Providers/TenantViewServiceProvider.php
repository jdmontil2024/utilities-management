<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Building;
use App\Models\Tenant;
use App\Models\Unit;

class TenantViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // ============================================
        // SHARE TENANT STATISTICS WITH DASHBOARDS
        // ============================================
        
        // Share tenant statistics with tenant dashboard/composer views
        View::composer(['tenants.index', 'tenants.partials.stats', 'dashboard.tenants'], function ($view) {
            $stats = [
                'total_active' => Tenant::active()->count(),
                'total_pending' => Tenant::where('lease_status', 'pending')->count(),
                'total_tenants' => Tenant::count(),
                'expiring_30_days' => Tenant::withExpiringLeases(30)->count(),
                'expiring_7_days' => Tenant::withExpiringLeases(7)->count(),
                'overdue' => Tenant::where('lease_status', 'active')
                                 ->whereDate('lease_end_date', '<', now())
                                 ->count(),
                'total_buildings' => Building::whereHas('activeTenants')->count(),
                'total_occupied_units' => Unit::whereHas('activeTenants')->count(),
                'occupancy_rate' => $this->getOverallOccupancyRate(),
                'avg_monthly_rent' => Tenant::active()->avg('monthly_rent') ?? 0,
                'total_monthly_revenue' => Tenant::active()->sum('monthly_rent') ?? 0,
            ];
            
            $view->with('tenantStats', $stats);
        });

        // ============================================
        // SHARE BUILDING OPTIONS FOR DROPDOWNS
        // ============================================

        // Share buildings list with tenant create/edit forms
        View::composer(['tenants.create', 'tenants.edit', 'tenants.partials.form'], function ($view) {
            $buildings = Building::orderBy('name')
                ->withCount(['units', 'activeTenants'])
                ->get()
                ->map(function ($building) {
                    $availableUnits = $building->units_count - $building->active_tenants_count;
                    
                    return [
                        'id' => $building->id,
                        'name' => $building->name,
                        'address' => $building->address,
                        'city' => $building->city,
                        'total_units' => $building->units_count,
                        'occupied_units' => $building->active_tenants_count,
                        'available_units' => max($availableUnits, 0),
                        'display_name' => $building->name . ' - ' . $building->city . ' (' . max($availableUnits, 0) . ' units available)',
                        'has_available_units' => $availableUnits > 0,
                    ];
                })
                ->filter(function ($building) {
                    // For edit forms, show all buildings; for create, only those with available units
                    if (View::getSection('_edit_mode') ?? false) {
                        return true;
                    }
                    return $building['has_available_units'];
                })
                ->values();
            
            $view->with('buildingOptions', $buildings);
        });

        // ============================================
        // SHARE FORM DROPDOWN OPTIONS
        // ============================================

        // Share lease status options with tenant forms
        View::composer(['tenants.create', 'tenants.edit', 'tenants.partials.form'], function ($view) {
            $leaseStatusOptions = [
                'active' => 'Active',
                'pending' => 'Pending',
                'expired' => 'Expired',
                'terminated' => 'Terminated',
            ];
            
            $leaseStatusColors = [
                'active' => 'success',
                'pending' => 'warning',
                'expired' => 'danger',
                'terminated' => 'secondary',
            ];
            
            $idTypeOptions = [
                'passport' => 'Passport',
                'driver_license' => 'Driver\'s License',
                'national_id' => 'National ID',
                'ssn' => 'Social Security Number',
                'tax_id' => 'Tax ID',
                'voter_id' => 'Voter\'s ID',
                'other' => 'Other',
            ];
            
            $occupantRelationOptions = [
                'spouse' => 'Spouse',
                'child' => 'Child',
                'parent' => 'Parent',
                'sibling' => 'Sibling',
                'roommate' => 'Roommate',
                'other' => 'Other',
            ];
            
            $view->with([
                'leaseStatusOptions' => $leaseStatusOptions,
                'leaseStatusColors' => $leaseStatusColors,
                'idTypeOptions' => $idTypeOptions,
                'occupantRelationOptions' => $occupantRelationOptions,
            ]);
        });

        // ============================================
        // SHARE BUILDING-SPECIFIC TENANT DATA
        // ============================================

        // Share recent tenants with building show page
        View::composer(['buildings.show', 'buildings.partials.tenants'], function ($view) {
            if (isset($view->getData()['building'])) {
                $building = $view->getData()['building'];
                
                $recentTenants = $building->tenants()
                    ->with(['unit', 'user'])
                    ->latest()
                    ->limit(5)
                    ->get();
                
                $expiringLeases = $building->expiringLeases()
                    ->with(['unit'])
                    ->get();
                
                $tenantStats = [
                    'total' => $building->activeTenants()->count(),
                    'expiring_30' => $building->expiringLeases()->count(),
                    'overdue' => $building->overdueLeases()->count(),
                    'monthly_revenue' => $building->total_monthly_tenant_revenue,
                ];
                
                $view->with([
                    'recentTenants' => $recentTenants,
                    'expiringLeases' => $expiringLeases,
                    'buildingTenantStats' => $tenantStats,
                ]);
            }
        });

        // ============================================
        // SHARE UNIT-SPECIFIC TENANT DATA
        // ============================================

        // Share tenant history with unit show page
        View::composer(['units.show', 'units.partials.tenant'], function ($view) {
            if (isset($view->getData()['unit'])) {
                $unit = $view->getData()['unit'];
                
                $currentTenant = $unit->currentTenant()
                    ->with(['user', 'paymentMethods'])
                    ->first();
                
                $tenantHistory = $unit->tenants()
                    ->with(['user'])
                    ->where('lease_status', '!=', 'active')
                    ->orWhere('id', '!=', optional($currentTenant)->id)
                    ->latest('lease_start_date')
                    ->limit(5)
                    ->get();
                
                $view->with([
                    'unitCurrentTenant' => $currentTenant,
                    'unitTenantHistory' => $tenantHistory,
                ]);
            }
        });

        // ============================================
        // SHARE DASHBOARD WIDGET DATA
        // ============================================

        // Share tenant widgets with main dashboard
        View::composer(['dashboard', 'dashboard.partials.tenant-widgets'], function ($view) {
            // Upcoming lease expirations
            $upcomingExpirations = Tenant::with(['unit.building', 'user'])
                ->withExpiringLeases(30)
                ->orderBy('lease_end_date')
                ->limit(10)
                ->get();
            
            // Recent move-ins (last 30 days)
            $recentMoveIns = Tenant::with(['unit.building', 'user'])
                ->where('lease_status', 'active')
                ->whereDate('lease_start_date', '>=', now()->subDays(30))
                ->orderBy('lease_start_date', 'desc')
                ->limit(5)
                ->get();
            
            // Tenants with pending documents
            $pendingDocuments = Tenant::with(['unit.building'])
                ->where('lease_status', 'pending')
                ->orWhereNull('government_id')
                ->limit(5)
                ->get();
            
            $view->with([
                'upcomingExpirations' => $upcomingExpirations,
                'recentMoveIns' => $recentMoveIns,
                'pendingDocuments' => $pendingDocuments,
            ]);
        });
    }

    /**
     * Calculate overall occupancy rate across all buildings.
     */
    private function getOverallOccupancyRate(): float
    {
        $totalUnits = Unit::count();
        if ($totalUnits === 0) {
            return 0;
        }
        
        $occupiedUnits = Unit::whereHas('activeTenants')->count();
        return round(($occupiedUnits / $totalUnits) * 100, 1);
    }

    /**
     * Get available units for a specific building (used in AJAX).
     */
    private function getAvailableUnits(int $buildingId): \Illuminate\Support\Collection
    {
        return Unit::where('building_id', $buildingId)
            ->whereIn('status', ['vacant', 'ready'])
            ->whereDoesntHave('activeTenants')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'monthly_rent', 'bedrooms', 'bathrooms', 'floor', 'area']);
    }
}