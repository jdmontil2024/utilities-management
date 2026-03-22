<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'total_floors',
        'total_units',
        'year_built',
        'building_type',
        'has_elevator',
        'has_parking',
        'total_area',
        'description',
        'contact_phone',
        'contact_email',
        'status',
    ];

    protected $casts = [
        'has_elevator' => 'boolean',
        'has_parking' => 'boolean',
        'total_area' => 'decimal:2',
        'total_floors' => 'integer',
        'total_units' => 'integer',
        'year_built' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============================================
    // EXISTING RELATIONSHIPS (KEEP ALL)
    // ============================================

    public function floorPlans()
    {
        return $this->hasMany(FloorPlan::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function occupiedUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'occupied');
    }

    public function vacantUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'vacant');
    }

    public function underMaintenanceUnits()
    {
        return $this->hasMany(Unit::class)->whereIn('status', ['maintenance', 'under_maintenance']);
    }

    public function photos()
    {
        return $this->hasMany(BuildingPhoto::class);
    }

    /**
     * Get all leases in this building through its units
     * FIXED: Added this relationship for monthly revenue calculation
     */
    public function leases()
    {
        return $this->hasManyThrough(
            Lease::class,
            Unit::class,
            'building_id', // Foreign key on units table
            'unit_id',     // Foreign key on leases table
            'id',          // Local key on buildings table
            'id'           // Local key on units table
        );
    }

    // ============================================
    // MAINTENANCE REQUESTS RELATIONSHIP
    // ============================================

    /**
     * Get all maintenance requests for this building through its units
     */
    public function maintenanceRequests()
    {
        return $this->hasManyThrough(
            MaintenanceRequest::class,
            Unit::class,
            'building_id', // Foreign key on units table
            'unit_id',     // Foreign key on maintenance_requests table
            'id',          // Local key on buildings table
            'id'           // Local key on units table
        )->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor', 'assignedStaff']);
    }

    /**
     * Get pending maintenance requests (not completed or cancelled)
     */
    public function pendingMaintenanceRequests()
    {
        return $this->maintenanceRequests()
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Get emergency maintenance requests
     */
    public function emergencyMaintenanceRequests()
    {
        return $this->maintenanceRequests()
                    ->where('priority', 'emergency')
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Get overdue maintenance requests
     */
    public function overdueMaintenanceRequests()
    {
        return $this->maintenanceRequests()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->whereHas('maintenanceCategory', function($q) {
                        $q->whereNotNull('sla_hours');
                    })
                    ->get()
                    ->filter(function($request) {
                        $slaHours = $request->maintenanceCategory->sla_hours ?? 48;
                        return $request->request_date->diffInHours(now()) > $slaHours;
                    });
    }

    /**
     * Get completed maintenance requests
     */
    public function completedMaintenanceRequests()
    {
        return $this->maintenanceRequests()->where('status', 'completed');
    }

    /**
     * Get maintenance requests count by status
     */
    public function getMaintenanceRequestsCountByStatusAttribute()
    {
        return [
            'total' => $this->maintenanceRequests()->count(),
            'pending' => $this->maintenanceRequests()->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'submitted' => $this->maintenanceRequests()->where('status', 'submitted')->count(),
            'assigned' => $this->maintenanceRequests()->where('status', 'assigned')->count(),
            'in_progress' => $this->maintenanceRequests()->where('status', 'in_progress')->count(),
            'completed' => $this->maintenanceRequests()->where('status', 'completed')->count(),
            'cancelled' => $this->maintenanceRequests()->where('status', 'cancelled')->count(),
            'emergency' => $this->maintenanceRequests()->where('priority', 'emergency')->count(),
            'high' => $this->maintenanceRequests()->where('priority', 'high')->count(),
            'medium' => $this->maintenanceRequests()->where('priority', 'medium')->count(),
            'low' => $this->maintenanceRequests()->where('priority', 'low')->count(),
            'overdue' => $this->overdueMaintenanceRequests()->count(),
        ];
    }

    /**
     * Get total estimated cost for pending maintenance
     */
    public function getPendingMaintenanceEstimatedCostAttribute()
    {
        return $this->maintenanceRequests()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->sum('estimated_cost');
    }

    /**
     * Get total actual cost for completed maintenance
     */
    public function getCompletedMaintenanceActualCostAttribute()
    {
        return $this->maintenanceRequests()
                    ->where('status', 'completed')
                    ->sum('actual_cost');
    }

    /**
     * Get maintenance requests summary
     */
    public function getMaintenanceSummaryAttribute()
    {
        return [
            'counts' => $this->maintenance_requests_count_by_status,
            'estimated_cost' => $this->pending_maintenance_estimated_cost,
            'actual_cost' => $this->completed_maintenance_actual_cost,
            'this_month' => $this->maintenanceRequests()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'last_month' => $this->maintenanceRequests()
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count(),
        ];
    }

    // ============================================
    // ENHANCED TENANT RELATIONSHIPS
    // ============================================

    /**
     * Get all tenants in this building
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * CRITICAL: Get all CURRENT tenants in this building with their units
     * This is used in Building Show page -> Tenants Tab
     */
    public function currentTenants()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('lease_end_date')
                          ->orWhere('lease_end_date', '>=', now());
                    })
                    ->with('unit')
                    ->orderBy('lease_start_date', 'desc');
    }

    /**
     * Get active tenants in this building
     */
    public function activeTenants()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->where('is_active', true);
    }

    /**
     * Get tenants with expiring leases (next 30 days)
     */
    public function expiringLeases()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->whereDate('lease_end_date', '>=', now())
                    ->whereDate('lease_end_date', '<=', now()->addDays(30));
    }

    /**
     * Get tenants with leases ending this month
     */
    public function leasesEndingThisMonth()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->whereMonth('lease_end_date', now()->month)
                    ->whereYear('lease_end_date', now()->year);
    }

    /**
     * Get tenants with overdue leases
     */
    public function overdueLeases()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->whereDate('lease_end_date', '<', now());
    }

    /**
     * Get tenants by unit type
     */
    public function tenantsByUnitType($unitType)
    {
        return $this->tenants()
                    ->whereHas('unit', function($q) use ($unitType) {
                        $q->where('unit_type', $unitType);
                    });
    }

    // ============================================
    // EAGER LOADING SCOPES FOR SHOW PAGE
    // ============================================

    /**
     * Scope to load building with all units and their current tenants
     */
    public function scopeWithUnitsAndTenants($query)
    {
        return $query->with([
            'units' => function($q) {
                $q->orderBy('unit_number');
            },
            'units.currentTenant',
            'units.currentLease'
        ]);
    }

    /**
     * Scope to load building with only current tenants
     */
    public function scopeWithCurrentTenants($query)
    {
        return $query->with(['currentTenants' => function($q) {
            $q->orderBy('lease_start_date', 'desc');
        }]);
    }

    /**
     * Scope to load building with maintenance requests
     */
    public function scopeWithMaintenanceRequests($query)
    {
        return $query->with(['maintenanceRequests' => function($q) {
            $q->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor', 'assignedStaff'])
              ->orderBy('created_at', 'desc');
        }]);
    }

    /**
     * Scope to load building with all necessary relations for show page
     */
    public function scopeWithShowPageRelations($query)
    {
        return $query->with([
            'units' => function($q) {
                $q->orderBy('unit_number');
            },
            'units.currentTenant',
            'units.currentLease',
            'currentTenants' => function($q) {
                $q->with('unit')->orderBy('lease_start_date', 'desc');
            },
            'maintenanceRequests' => function($q) {
                $q->with(['unit', 'tenant', 'maintenanceCategory', 'assignedVendor', 'assignedStaff'])
                  ->orderBy('created_at', 'desc');
            },
            'photos'
        ]);
    }

    // ============================================
    // EXISTING ACCESSORS & METHODS (KEEP ALL)
    // ============================================

    /**
     * Calculate occupancy rate for the building
     */
    public function getOccupancyRateAttribute()
    {
        if ($this->total_units == 0) {
            return 0;
        }

        $occupiedCount = $this->occupiedUnits()->count();
        return ($occupiedCount / $this->total_units) * 100;
    }

    /**
     * Calculate total monthly rental income
     */
    public function getMonthlyRentalIncomeAttribute()
    {
        return $this->occupiedUnits()->sum('monthly_rent');
    }

    /**
     * Calculate total potential monthly rental income
     */
    public function getPotentialMonthlyIncomeAttribute()
    {
        return $this->units()->sum('monthly_rent');
    }

    /**
     * Get active leases count
     */
    public function getActiveLeasesCountAttribute()
    {
        $total = 0;
        foreach ($this->units as $unit) {
            $total += $unit->leases()->where('lease_status', 'active')->count();
        }
        return $total;
    }

    // ============================================
    // NEW/ENHANCED TENANT ACCESSORS
    // ============================================

    /**
     * Get total units count (dynamic)
     */
    public function getTotalUnitsCountAttribute()
    {
        return $this->units()->count();
    }

    /**
     * Get occupied units count (dynamic)
     */
    public function getOccupiedUnitsCountAttribute()
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    /**
     * Get vacant units count (dynamic)
     */
    public function getVacantUnitsCountAttribute()
    {
        return $this->units()->whereIn('status', ['vacant', 'ready'])->count();
    }

    /**
     * Get total number of active tenants
     */
    public function getTotalTenantsCountAttribute()
    {
        return $this->activeTenants()->count();
    }

    /**
     * Get total number of tenants (including inactive)
     */
    public function getAllTenantsCountAttribute()
    {
        return $this->tenants()->count();
    }

    /**
     * Get number of expiring leases
     */
    public function getExpiringLeasesCountAttribute()
    {
        return $this->expiringLeases()->count();
    }

    /**
     * Get number of leases ending this month
     */
    public function getLeasesEndingThisMonthCountAttribute()
    {
        return $this->leasesEndingThisMonth()->count();
    }

    /**
     * Get number of overdue leases
     */
    public function getOverdueLeasesCountAttribute()
    {
        return $this->overdueLeases()->count();
    }

    /**
     * Get tenant occupancy rate (units with active tenants)
     */
    public function getTenantOccupancyRateAttribute()
    {
        $total = $this->total_units_count;
        if ($total == 0) {
            return 0;
        }

        $tenantOccupiedCount = $this->activeTenants()->count();
        return min(round(($tenantOccupiedCount / $total) * 100, 1), 100);
    }

    /**
     * Get average monthly rent from active tenants
     */
    public function getAverageTenantRentAttribute()
    {
        $activeTenants = $this->activeTenants();
        
        if ($activeTenants->count() === 0) {
            return 0;
        }
        
        return $activeTenants->avg('monthly_rent');
    }

    /**
     * Get total monthly revenue from active tenants
     */
    public function getTotalMonthlyTenantRevenueAttribute()
    {
        return $this->activeTenants()->sum('monthly_rent');
    }

    /**
     * Get total monthly revenue from all leases (active and pending)
     */
    public function getTotalMonthlyLeaseRevenueAttribute()
    {
        return $this->leases()
                    ->whereIn('lease_status', ['active', 'pending'])
                    ->sum('monthly_rent');
    }

    /**
     * Get total security deposits held
     */
    public function getTotalSecurityDepositsAttribute()
    {
        return $this->activeTenants()->sum('security_deposit');
    }

    /**
     * Get monthly revenue (alias for total_monthly_tenant_revenue)
     */
    public function getMonthlyRevenueAttribute()
    {
        return $this->total_monthly_tenant_revenue;
    }

    // ============================================
    // EXISTING TENANT STATISTICS METHODS
    // ============================================

    /**
     * Get tenant demographics by unit type
     */
    public function getTenantsByUnitTypeStatsAttribute()
    {
        $stats = [];
        $unitTypes = $this->units()->select('unit_type')->distinct()->pluck('unit_type');
        
        foreach ($unitTypes as $unitType) {
            $count = $this->tenantsByUnitType($unitType)->count();
            $stats[$unitType] = [
                'count' => $count,
                'unit_type_label' => $this->getUnitTypeLabelStatic($unitType) ?? ucfirst($unitType)
            ];
        }
        
        return $stats;
    }

    /**
     * Helper method for unit type labels
     */
    protected function getUnitTypeLabelStatic($unitType)
    {
        $typeLabels = [
            'studio' => 'Studio',
            '1br' => '1 Bedroom',
            '2br' => '2 Bedrooms',
            '3br' => '3 Bedrooms',
            'commercial' => 'Commercial',
            'other' => 'Other',
            'apartment' => 'Apartment',
            'penthouse' => 'Penthouse',
            'loft' => 'Loft',
            'office' => 'Office',
            'retail' => 'Retail Space',
            'storage' => 'Storage Unit',
            'parking' => 'Parking Space',
        ];
        
        return $typeLabels[$unitType] ?? ucfirst($unitType);
    }

    /**
     * Get lease expiration timeline
     */
    public function getLeaseExpirationTimelineAttribute()
    {
        $timeline = [
            'this_month' => $this->leasesEndingThisMonth()->count(),
            'next_30_days' => $this->expiringLeases()->count(),
            'next_60_days' => $this->tenants()
                ->where('lease_status', 'active')
                ->whereDate('lease_end_date', '>', now()->addDays(30))
                ->whereDate('lease_end_date', '<=', now()->addDays(60))
                ->count(),
            'next_90_days' => $this->tenants()
                ->where('lease_status', 'active')
                ->whereDate('lease_end_date', '>', now()->addDays(60))
                ->whereDate('lease_end_date', '<=', now()->addDays(90))
                ->count(),
            'overdue' => $this->overdueLeases()->count(),
        ];
        
        return $timeline;
    }

    /**
     * Get tenant summary for dashboard
     */
    public function getTenantSummaryAttribute()
    {
        return [
            'total_active' => $this->total_tenants_count,
            'total_all' => $this->all_tenants_count,
            'expiring_soon' => $this->expiring_leases_count,
            'ending_this_month' => $this->leases_ending_this_month_count,
            'overdue' => $this->overdue_leases_count,
            'occupancy_rate' => round($this->tenant_occupancy_rate, 1),
            'monthly_revenue' => $this->total_monthly_tenant_revenue,
            'avg_rent' => $this->average_tenant_rent,
            'security_deposits' => $this->total_security_deposits,
        ];
    }

    // ============================================
    // EXISTING SCOPES (KEEP ALL)
    // ============================================

    /**
     * Scope for active buildings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for buildings with available units
     */
    public function scopeWithAvailableUnits($query)
    {
        return $query->whereHas('units', function($q) {
            $q->where('status', 'vacant');
        });
    }

    // ============================================
    // ENHANCED TENANT SCOPES
    // ============================================

    /**
     * Scope: Buildings with active tenants
     */
    public function scopeWithActiveTenants($query)
    {
        return $query->whereHas('tenants', function($q) {
            $q->where('lease_status', 'active')
              ->where('is_active', true);
        });
    }

    /**
     * Scope: Buildings with expiring leases
     */
    public function scopeWithExpiringLeases($query, $days = 30)
    {
        return $query->whereHas('tenants', function($q) use ($days) {
            $q->where('lease_status', 'active')
              ->whereDate('lease_end_date', '>=', now())
              ->whereDate('lease_end_date', '<=', now()->addDays($days));
        });
    }

    /**
     * Scope: Buildings with overdue leases
     */
    public function scopeWithOverdueLeases($query)
    {
        return $query->whereHas('tenants', function($q) {
            $q->where('lease_status', 'active')
              ->whereDate('lease_end_date', '<', now());
        });
    }

    /**
     * Scope: Buildings by tenant count
     */
    public function scopeHavingTenantCount($query, $operator, $count)
    {
        return $query->withCount('activeTenants')
                     ->having('active_tenants_count', $operator, $count);
    }

    /**
     * Scope: Buildings with high occupancy (>90%)
     */
    public function scopeHighOccupancy($query)
    {
        return $query->withCount(['units', 'activeTenants'])
                     ->havingRaw('(COALESCE(active_tenants_count, 0) * 1.0 / NULLIF(units_count, 0)) >= 0.9');
    }

    /**
     * Scope: Buildings with low occupancy (<50%)
     */
    public function scopeLowOccupancy($query)
    {
        return $query->withCount(['units', 'activeTenants'])
                     ->havingRaw('(COALESCE(active_tenants_count, 0) * 1.0 / NULLIF(units_count, 0)) <= 0.5');
    }

    /**
     * Scope: Search buildings
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('address', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%")
              ->orWhere('contact_email', 'like', "%{$search}%")
              ->orWhere('contact_phone', 'like', "%{$search}%");
        });
    }

    // ============================================
    // MAINTENANCE SCOPES
    // ============================================

    /**
     * Scope: Buildings with pending maintenance requests
     */
    public function scopeWithPendingMaintenance($query)
    {
        return $query->whereHas('maintenanceRequests', function($q) {
            $q->whereNotIn('status', ['completed', 'cancelled']);
        });
    }

    /**
     * Scope: Buildings with emergency maintenance
     */
    public function scopeWithEmergencyMaintenance($query)
    {
        return $query->whereHas('maintenanceRequests', function($q) {
            $q->where('priority', 'emergency')
              ->whereNotIn('status', ['completed', 'cancelled']);
        });
    }

    /**
     * Scope: Buildings with overdue maintenance
     */
    public function scopeWithOverdueMaintenance($query)
    {
        return $query->whereHas('maintenanceRequests', function($q) {
            $q->whereNotIn('status', ['completed', 'cancelled'])
              ->whereHas('maintenanceCategory', function($sq) {
                  $sq->whereNotNull('sla_hours');
              });
        });
    }

    // ============================================
    // EXISTING HELPER METHODS (KEEP ALL)
    // ============================================

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        if ($this->zip_code) {
            $address .= ' ' . $this->zip_code;
        }
        if ($this->country && $this->country !== 'US') {
            $address .= ', ' . $this->country;
        }
        return $address;
    }

    /**
     * Get building type label
     */
    public function getBuildingTypeLabelAttribute()
    {
        $typeLabels = [
            'residential' => 'Residential',
            'commercial' => 'Commercial',
            'mixed' => 'Mixed-Use',
            'office' => 'Office',
            'retail' => 'Retail',
            'industrial' => 'Industrial',
        ];
        
        return $typeLabels[$this->building_type] ?? ucfirst($this->building_type);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'under_construction' => 'Under Construction',
            'renovation' => 'Renovation',
            'sold' => 'Sold',
            'closed' => 'Closed',
        ];
        
        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get building icon based on type
     */
    public function getIconAttribute()
    {
        $icons = [
            'residential' => '🏠',
            'commercial' => '🏢',
            'mixed' => '🏙️',
            'office' => '💼',
            'retail' => '🛍️',
            'industrial' => '🏭',
        ];
        
        return $icons[$this->building_type] ?? '🏢';
    }

    // ============================================
    // REVENUE STATISTICS METHODS
    // ============================================

    /**
     * Get revenue by month
     */
    public function revenueByMonth($year = null)
    {
        $year = $year ?? now()->year;
        
        return $this->units()
                    ->join('leases', 'units.id', '=', 'leases.unit_id')
                    ->join('tenants', 'leases.tenant_id', '=', 'tenants.id')
                    ->where('leases.lease_status', 'active')
                    ->whereYear('leases.start_date', '<=', $year)
                    ->where(function($q) use ($year) {
                        $q->whereYear('leases.end_date', '>=', $year)
                          ->orWhereNull('leases.end_date');
                    })
                    ->select(
                        DB::raw('MONTH(leases.start_date) as month'),
                        DB::raw('SUM(units.monthly_rent) as total')
                    )
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get()
                    ->pluck('total', 'month');
    }

    /**
     * Get units by type
     */
    public function unitsByType()
    {
        return $this->units()
                    ->select('unit_type', DB::raw('count(*) as count'))
                    ->groupBy('unit_type')
                    ->orderBy('count', 'desc')
                    ->get();
    }

    /**
     * Get units by status
     */
    public function unitsByStatus()
    {
        return $this->units()
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->orderBy('count', 'desc')
                    ->get();
    }

    /**
     * Get tenants by lease status
     */
    public function tenantsByStatus()
    {
        return $this->tenants()
                    ->select('lease_status', DB::raw('count(*) as count'))
                    ->groupBy('lease_status')
                    ->orderBy('count', 'desc')
                    ->get();
    }

    /**
     * Get maintenance requests by priority
     */
    public function maintenanceRequestsByPriority()
    {
        return $this->maintenanceRequests()
                    ->select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->orderBy('count', 'desc')
                    ->get();
    }

    /**
     * Get maintenance requests by status
     */
    public function maintenanceRequestsByStatus()
    {
        return $this->maintenanceRequests()
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->orderBy('count', 'desc')
                    ->get();
    }

    // ============================================
    // API SUMMARY METHOD
    // ============================================

    /**
     * Generate a summary array for API responses
     */
    public function toSummaryArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'full_address' => $this->full_address,
            'building_type' => $this->building_type,
            'building_type_label' => $this->building_type_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'icon' => $this->icon,
            'total_units' => $this->total_units_count,
            'occupied_units' => $this->occupied_units_count,
            'vacant_units' => $this->vacant_units_count,
            'total_floors' => $this->total_floors,
            'occupancy_rate' => round($this->occupancy_rate, 1),
            'monthly_income' => $this->monthly_rental_income,
            'potential_income' => $this->potential_monthly_income,
            'active_leases' => $this->active_leases_count,
            'total_tenants' => $this->total_tenants_count,
            'tenant_occupancy_rate' => round($this->tenant_occupancy_rate, 1),
            'expiring_leases' => $this->expiring_leases_count,
            'monthly_tenant_revenue' => $this->total_monthly_tenant_revenue,
            'maintenance_summary' => $this->maintenance_summary,
            'has_photos' => $this->photos()->exists(),
            'primary_photo' => $this->photos()->where('is_primary', true)->first()?->path,
        ];
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // When building is created, set default total_units to 0 if not provided
        static::creating(function($building) {
            if (is_null($building->total_units)) {
                $building->total_units = 0;
            }
        });

        // When building is deleted, also delete related units
        static::deleting(function($building) {
            if ($building->isForceDeleting()) {
                $building->units()->forceDelete();
                $building->floorPlans()->forceDelete();
                $building->photos()->forceDelete();
                $building->maintenanceRequests()->forceDelete();
                $building->leases()->forceDelete();
            } else {
                $building->units()->delete();
                $building->maintenanceRequests()->delete();
                $building->leases()->delete();
            }
        });

        // When building is restored, restore related units
        static::restored(function($building) {
            $building->units()->withTrashed()->restore();
            $building->maintenanceRequests()->withTrashed()->restore();
            $building->leases()->withTrashed()->restore();
        });
    }
}