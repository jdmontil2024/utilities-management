<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'floor_plan_id',
        'unit_number',
        'unit_name',
        'floor',
        'area',
        'bedrooms',
        'bathrooms',
        'unit_type',
        'status',
        'monthly_rent',
        'security_deposit',
        'parking_fee',
        'amenities',
        'description',
        'features',
        'year_renovated',
        'available_date',
        'notes',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'parking_fee' => 'decimal:2',
        'amenities' => 'array',
        'features' => 'array',
        'available_date' => 'date',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'floor' => 'integer',
        'year_renovated' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============================================
    // EXISTING RELATIONSHIPS (KEEP ALL YOUR EXISTING ONES)
    // ============================================

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function floorPlan()
    {
        return $this->belongsTo(FloorPlan::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function preventiveMaintenances()
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class);
    }

    public function electricityReadings()
    {
        return $this->hasMany(ElectricityReading::class);
    }

    public function waterReadings()
    {
        return $this->hasMany(WaterReading::class);
    }

    public function gasReadings()
    {
        return $this->hasMany(GasReading::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    // ============================================
    // FIXED/OPTIMIZED TENANT RELATIONSHIPS
    // ============================================

    /**
     * Get the current active tenant for this unit - OPTIMIZED FOR SHOW PAGE
     * This is the PRIMARY relationship used in Unit Show page
     */
    public function currentTenant()
    {
        return $this->hasOne(Tenant::class)
                    ->where('lease_status', 'active')
                    ->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('lease_end_date')
                          ->orWhere('lease_end_date', '>=', now());
                    })
                    ->latest('lease_start_date');
    }

    /**
     * Get all tenants (past and present) for this unit
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class)->orderBy('lease_start_date', 'desc');
    }

    /**
     * Get all active tenants for this unit
     */
    public function activeTenants()
    {
        return $this->hasMany(Tenant::class)
                    ->where('lease_status', 'active')
                    ->where('is_active', true);
    }

    // ============================================
    // NEW/IMPROVED LEASE RELATIONSHIPS
    // ============================================

    /**
     * Get current active lease - OPTIMIZED FOR UNIT SHOW PAGE
     */
    public function currentLease()
    {
        return $this->hasOne(Lease::class)
                    ->where('lease_status', 'active')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->latest('start_date');
    }

    /**
     * Get current tenant through active lease (ALTERNATIVE METHOD)
     */
    public function tenantThroughLease()
    {
        return $this->hasOneThrough(
            Tenant::class,
            Lease::class,
            'unit_id',      // Foreign key on leases table
            'id',           // Foreign key on tenants table
            'id',           // Local key on units table
            'tenant_id'     // Local key on leases table
        )->where('leases.lease_status', 'active')
         ->whereDate('leases.start_date', '<=', now())
         ->whereDate('leases.end_date', '>=', now())
         ->latest('leases.start_date');
    }

    // ============================================
    // SCOPES - ADD EAGER LOADING SCOPES
    // ============================================

    /**
     * Scope to eager load current tenant and lease - CRITICAL FOR SHOW PAGE
     */
    public function scopeWithCurrentTenant($query)
    {
        return $query->with(['currentTenant', 'currentLease']);
    }

    /**
     * Scope to eager load all necessary relationships for show page
     */
    public function scopeWithShowPageRelations($query)
    {
        return $query->with([
            'building',
            'currentTenant',
            'currentLease',
            'maintenanceRequests' => function($q) {
                $q->latest()->limit(10);
            }
        ]);
    }

    // ============================================
    // KEEP ALL YOUR EXISTING SCOPES (ALL OF THEM)
    // ============================================
    
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeVacant($query)
    {
        return $query->where('status', 'vacant');
    }

    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeUnderRenovation($query)
    {
        return $query->where('status', 'renovation');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['vacant', 'ready']);
    }

    public function scopeWithActiveLease($query)
    {
        return $query->whereHas('leases', function ($q) {
            $q->where('lease_status', 'active');
        });
    }

    public function scopeWithActiveTenant($query)
    {
        return $query->whereHas('tenants', function ($q) {
            $q->where('lease_status', 'active')
              ->where('is_active', true);
        });
    }

    public function scopeInBuilding($query, $buildingId)
    {
        return $query->where('building_id', $buildingId);
    }

    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    public function scopeOfType($query, $unitType)
    {
        return $query->where('unit_type', $unitType);
    }

    public function scopeWithAmenities($query, array $amenities)
    {
        foreach ($amenities as $amenity) {
            $query->whereJsonContains('amenities', $amenity);
        }
        return $query;
    }

    public function scopeWithFeatures($query, array $features)
    {
        foreach ($features as $feature) {
            $query->whereJsonContains('features', $feature);
        }
        return $query;
    }

    public function scopeRentBetween($query, $min, $max)
    {
        return $query->whereBetween('monthly_rent', [$min, $max]);
    }

    public function scopeBedrooms($query, $count)
    {
        return $query->where('bedrooms', $count);
    }

    public function scopeBathrooms($query, $count)
    {
        return $query->where('bathrooms', $count);
    }

    // ============================================
    // KEEP ALL YOUR EXISTING ACCESSORS (ALL OF THEM)
    // ============================================

    public function getFullAddressAttribute()
    {
        if ($this->building) {
            return "Unit {$this->unit_number}, {$this->building->address}";
        }
        return "Unit {$this->unit_number}";
    }

    public function getRentFormattedAttribute()
    {
        return '$' . number_format($this->monthly_rent, 2);
    }

    public function getAreaFormattedAttribute()
    {
        return $this->area ? number_format($this->area, 0) . ' sq ft' : 'N/A';
    }

    public function getSizeFormattedAttribute()
    {
        return $this->getAreaFormattedAttribute();
    }

    public function getStatusLabelAttribute()
    {
        $statusLabels = [
            'vacant' => 'Vacant',
            'occupied' => 'Occupied',
            'maintenance' => 'Under Maintenance',
            'renovation' => 'Under Renovation',
            'reserved' => 'Reserved',
            'ready' => 'Ready for Occupancy',
            'under_maintenance' => 'Under Maintenance',
        ];

        return $statusLabels[$this->status] ?? ucfirst($this->status);
    }

    public function getUnitTypeLabelAttribute()
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

        return $typeLabels[$this->unit_type] ?? ucfirst($this->unit_type);
    }

    public function getTypeLabelAttribute()
    {
        return $this->unit_type_label;
    }

    public function getTotalMonthlyAmountAttribute()
    {
        return $this->monthly_rent + ($this->parking_fee ?? 0);
    }

    public function getTotalMonthlyFormattedAttribute()
    {
        return '$' . number_format($this->total_monthly_amount, 2);
    }

    public function getSecurityDepositFormattedAttribute()
    {
        return $this->security_deposit ? '$' . number_format($this->security_deposit, 2) : 'N/A';
    }

    public function getParkingFeeFormattedAttribute()
    {
        return $this->parking_fee ? '$' . number_format($this->parking_fee, 2) . '/month' : 'N/A';
    }

    public function getIsOccupiedAttribute()
    {
        return $this->status === 'occupied' || $this->currentTenant()->exists();
    }

    public function getIsAvailableAttribute()
    {
        return in_array($this->status, ['vacant', 'ready']) && !$this->currentTenant()->exists();
    }

    public function getIsUnderMaintenanceAttribute()
    {
        return in_array($this->status, ['maintenance', 'under_maintenance']);
    }

    public function getIsReservedAttribute()
    {
        return $this->status === 'reserved';
    }

    /**
     * Get current tenant name - IMPROVED TO USE RELATIONSHIP
     */
    public function getCurrentTenantNameAttribute()
    {
        if ($this->relationLoaded('currentTenant') && $this->currentTenant) {
            return $this->currentTenant->full_name;
        }
        
        $tenant = $this->currentTenant()->first();
        return $tenant ? $tenant->full_name : null;
    }

    /**
     * Get current tenant ID - IMPROVED TO USE RELATIONSHIP
     */
    public function getCurrentTenantIdAttribute()
    {
        if ($this->relationLoaded('currentTenant') && $this->currentTenant) {
            return $this->currentTenant->id;
        }
        
        $tenant = $this->currentTenant()->first();
        return $tenant ? $tenant->id : null;
    }

    /**
     * Get lease end date for current tenant - IMPROVED
     */
    public function getCurrentLeaseEndDateAttribute()
    {
        if ($this->relationLoaded('currentLease') && $this->currentLease) {
            return $this->currentLease->end_date;
        }
        
        if ($this->relationLoaded('currentTenant') && $this->currentTenant) {
            return $this->currentTenant->lease_end_date;
        }
        
        $tenant = $this->currentTenant()->first();
        return $tenant ? $tenant->lease_end_date : null;
    }

    public function getIsLeaseExpiringSoonAttribute($days = 30)
    {
        $leaseEndDate = $this->current_lease_end_date;
        if (!$leaseEndDate) {
            return false;
        }
        
        $diff = now()->diffInDays($leaseEndDate, false);
        return $diff <= $days && $diff > 0;
    }

    public function getFeaturesListAttribute()
    {
        if (!$this->features || !is_array($this->features)) {
            return [];
        }

        $featureLabels = [
            'balcony' => 'Balcony/Patio',
            'fireplace' => 'Fireplace',
            'hardwood' => 'Hardwood Floors',
            'carpet' => 'Carpet',
            'central_ac' => 'Central A/C',
            'washer_dryer' => 'Washer/Dryer',
            'dishwasher' => 'Dishwasher',
            'disposal' => 'Garbage Disposal',
            'microwave' => 'Microwave',
            'refrigerator' => 'Refrigerator',
            'oven' => 'Oven/Range',
            'granite' => 'Granite Countertops',
            'marble' => 'Marble Bathroom',
            'walkin_closet' => 'Walk-in Closet',
            'storage' => 'Extra Storage',
            'parking' => 'Parking Included',
            'gym' => 'Gym Access',
            'pool' => 'Pool Access',
            'concierge' => 'Concierge',
            'security' => '24/7 Security',
            'elevator' => 'Elevator',
            'wheelchair' => 'Wheelchair Accessible',
            'pets_allowed' => 'Pets Allowed',
            'smoking' => 'Smoking Allowed',
            'furnished' => 'Furnished',
            'unfurnished' => 'Unfurnished',
            'utilities_included' => 'Utilities Included',
            'internet_included' => 'Internet Included',
            'cable_included' => 'Cable TV Included',
        ];

        $features = [];
        foreach ($this->features as $feature) {
            if (isset($featureLabels[$feature])) {
                $features[] = $featureLabels[$feature];
            } else {
                $features[] = ucfirst(str_replace('_', ' ', $feature));
            }
        }

        return $features;
    }

    public function getAmenitiesListAttribute()
    {
        if (!$this->amenities || !is_array($this->amenities)) {
            return [];
        }

        $amenityLabels = [
            'pool' => 'Swimming Pool',
            'gym' => 'Fitness Center',
            'parking' => 'Parking',
            'laundry' => 'Laundry Facilities',
            'elevator' => 'Elevator',
            'security' => 'Security System',
            'concierge' => 'Concierge Service',
            'clubhouse' => 'Clubhouse',
            'playground' => 'Playground',
            'bbq' => 'BBQ Area',
            'gardens' => 'Gardens',
            'rooftop' => 'Rooftop Terrace',
            'business_center' => 'Business Center',
            'package_lockers' => 'Package Lockers',
            'pet_area' => 'Pet Area',
            'bike_storage' => 'Bike Storage',
            'storage_units' => 'Storage Units',
            'tennis_court' => 'Tennis Court',
            'basketball_court' => 'Basketball Court',
            'sauna' => 'Sauna',
            'spa' => 'Spa',
            'movie_theater' => 'Movie Theater',
            'game_room' => 'Game Room',
            'party_room' => 'Party Room',
            'guest_suite' => 'Guest Suite',
            'valet_trash' => 'Valet Trash Service',
        ];

        $amenities = [];
        foreach ($this->amenities as $amenity) {
            if (isset($amenityLabels[$amenity])) {
                $amenities[] = $amenityLabels[$amenity];
            } else {
                $amenities[] = ucfirst(str_replace('_', ' ', $amenity));
            }
        }

        return $amenities;
    }

    // ============================================
    // KEEP ALL YOUR EXISTING MUTATORS
    // ============================================

    public function setAmenitiesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['amenities'] = json_encode(array_filter($value));
        } elseif (is_null($value)) {
            $this->attributes['amenities'] = null;
        } else {
            $this->attributes['amenities'] = $value;
        }
    }

    public function setFeaturesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['features'] = json_encode(array_filter($value));
        } elseif (is_null($value)) {
            $this->attributes['features'] = null;
        } else {
            $this->attributes['features'] = $value;
        }
    }

    public function setUnitNumberAttribute($value)
    {
        $this->attributes['unit_number'] = strtoupper(trim($value));
    }

    public function setMonthlyRentAttribute($value)
    {
        $this->attributes['monthly_rent'] = (float) $value;
    }

    public function setSecurityDepositAttribute($value)
    {
        $this->attributes['security_deposit'] = $value ? (float) $value : 0;
    }

    public function setParkingFeeAttribute($value)
    {
        $this->attributes['parking_fee'] = $value ? (float) $value : 0;
    }

    // ============================================
    // KEEP ALL YOUR EXISTING BUSINESS LOGIC METHODS
    // ============================================

    public function occupancyRate($period = 'month')
    {
        return $this->is_occupied ? 100 : 0;
    }

    public function totalRevenue($startDate = null, $endDate = null)
    {
        $query = $this->bills()->where('status', 'paid');
        
        if ($startDate) {
            $query->where('due_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('due_date', '<=', $endDate);
        }
        
        return $query->sum('total_amount');
    }

    public function pendingBillsTotal()
    {
        return $this->bills()->where('status', 'pending')->sum('total_amount');
    }

    public function overdueBillsTotal()
    {
        return $this->bills()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->sum('total_amount');
    }

    public function openMaintenanceCount()
    {
        return $this->maintenanceRequests()
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function hasActiveIssues()
    {
        return $this->status === 'maintenance' || 
               $this->openMaintenanceCount() > 0 ||
               $this->overdueBillsTotal() > 0;
    }

    public function lastMeterReadingDate($utilityType)
    {
        $reading = $this->meterReadings()
            ->whereHas('utilityType', function ($q) use ($utilityType) {
                $q->where('type', $utilityType);
            })
            ->latest('reading_date')
            ->first();
            
        return $reading ? $reading->reading_date : null;
    }

    public function averageMonthlyConsumption($utilityType, $months = 6)
    {
        $consumptions = $this->consumptions()
            ->whereHas('utilityType', function ($q) use ($utilityType) {
                $q->where('type', $utilityType);
            })
            ->where('period', '>=', now()->subMonths($months))
            ->get();
            
        if ($consumptions->isEmpty()) {
            return 0;
        }
        
        return $consumptions->avg('consumption');
    }

    public function yearlyRevenueProjection()
    {
        return $this->total_monthly_amount * 12;
    }

    public function getNextAvailableDateAttribute()
    {
        if ($this->available_date && $this->available_date > now()) {
            return $this->available_date;
        }
        
        if ($this->is_available) {
            return now();
        }
        
        return null;
    }

    public function hasUpcomingReservations()
    {
        return $this->leases()
            ->where('lease_status', 'reserved')
            ->where('start_date', '>', now())
            ->exists();
    }

    public function getTenantHistoryAttribute()
    {
        return $this->tenants()
            ->with('user')
            ->orderBy('lease_start_date', 'desc')
            ->get();
    }

    // ============================================
    // KEEP ALL YOUR EXISTING HELPER METHODS
    // ============================================

    public function getDisplayNameAttribute()
    {
        if ($this->unit_name) {
            return "Unit {$this->unit_number} - {$this->unit_name}";
        }
        return "Unit {$this->unit_number}";
    }

    public function getShortDescriptionAttribute()
    {
        $desc = $this->unit_type_label;
        
        if ($this->bedrooms || $this->bathrooms) {
            $desc .= ' · ';
            if ($this->bedrooms) {
                $desc .= $this->bedrooms . ' BR';
            }
            if ($this->bathrooms) {
                $desc .= ($this->bedrooms ? '/' : '') . $this->bathrooms . ' BA';
            }
        }
        
        return $desc;
    }

    public function getOccupancyStatusAttribute()
    {
        $statuses = [
            'occupied' => ['label' => 'Occupied', 'color' => 'success', 'badge' => 'bg-green-100 text-green-800'],
            'vacant' => ['label' => 'Vacant', 'color' => 'secondary', 'badge' => 'bg-gray-100 text-gray-800'],
            'maintenance' => ['label' => 'Maintenance', 'color' => 'warning', 'badge' => 'bg-yellow-100 text-yellow-800'],
            'renovation' => ['label' => 'Renovation', 'color' => 'info', 'badge' => 'bg-blue-100 text-blue-800'],
            'reserved' => ['label' => 'Reserved', 'color' => 'primary', 'badge' => 'bg-purple-100 text-purple-800'],
            'ready' => ['label' => 'Ready', 'color' => 'success', 'badge' => 'bg-green-100 text-green-800'],
            'under_maintenance' => ['label' => 'Maintenance', 'color' => 'warning', 'badge' => 'bg-yellow-100 text-yellow-800'],
        ];
        
        return $statuses[$this->status] ?? ['label' => ucfirst($this->status), 'color' => 'light', 'badge' => 'bg-gray-100 text-gray-800'];
    }

    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'occupied' => 'badge-success',
            'vacant' => 'badge-secondary',
            'maintenance' => 'badge-warning',
            'renovation' => 'badge-info',
            'reserved' => 'badge-primary',
            'ready' => 'badge-success',
            'under_maintenance' => 'badge-warning',
        ];
        
        return $classes[$this->status] ?? 'badge-light';
    }

    public function getUnitTypeIconAttribute()
    {
        $icons = [
            'studio' => '🏠',
            '1br' => '🏠',
            '2br' => '🏠',
            '3br' => '🏠',
            'commercial' => '🏢',
            'other' => '🏠',
            'apartment' => '🏢',
            'penthouse' => '🏙️',
            'loft' => '🏭',
            'office' => '💼',
            'retail' => '🛍️',
            'storage' => '📦',
            'parking' => '🅿️',
        ];
        
        return $icons[$this->unit_type] ?? '🏠';
    }

    public function getAvailableDateFormattedAttribute()
    {
        if (!$this->available_date) {
            return 'Immediate';
        }
        
        return $this->available_date->format('M d, Y');
    }

    public function getDaysUntilAvailableAttribute()
    {
        if (!$this->available_date) {
            return 0;
        }
        
        return now()->diffInDays($this->available_date, false);
    }

    public function getIsAvailableNowAttribute()
    {
        if (!$this->available_date) {
            return $this->is_available;
        }
        
        return $this->is_available && $this->available_date <= now();
    }

    public function toSummaryArray()
    {
        return [
            'id' => $this->id,
            'unit_number' => $this->unit_number,
            'unit_name' => $this->unit_name,
            'display_name' => $this->display_name,
            'building_id' => $this->building_id,
            'building_name' => $this->building ? $this->building->name : null,
            'floor' => $this->floor,
            'area' => $this->area,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'unit_type' => $this->unit_type,
            'unit_type_label' => $this->unit_type_label,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'monthly_rent' => $this->monthly_rent,
            'rent_formatted' => $this->rent_formatted,
            'total_monthly_amount' => $this->total_monthly_amount,
            'total_monthly_formatted' => $this->total_monthly_formatted,
            'is_available' => $this->is_available,
            'is_occupied' => $this->is_occupied,
            'available_date' => $this->available_date,
            'available_date_formatted' => $this->available_date_formatted,
            'is_available_now' => $this->is_available_now,
            'has_active_lease' => $this->currentLease()->exists(),
            'current_tenant_name' => $this->current_tenant_name,
            'current_tenant_id' => $this->current_tenant_id,
            'current_lease_end_date' => $this->current_lease_end_date,
            'is_lease_expiring_soon' => $this->is_lease_expiring_soon,
        ];
    }

    // ============================================
    // NEW: HELPER METHOD TO UPDATE STATUS FROM LEASES
    // ============================================

    /**
     * Update unit status based on active leases
     */
    public function updateStatusFromLeases(): void
    {
        $hasActiveLease = Lease::where('unit_id', $this->id)
            ->where('lease_status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->exists();

        if ($hasActiveLease) {
            if ($this->status !== 'occupied') {
                $this->update(['status' => 'occupied']);
            }
        } else {
            if (!in_array($this->status, ['vacant', 'ready'])) {
                $this->update(['status' => 'vacant']);
            }
        }
    }

    // ============================================
    // BOOT METHOD
    // ============================================

    protected static function boot()
    {
        parent::boot();

        // When a unit is created, set default status if not provided
        static::creating(function ($unit) {
            if (!$unit->status) {
                $unit->status = 'vacant';
            }
        });

        // When a unit is updated, check if status should change based on tenants
        static::updated(function ($unit) {
            if ($unit->isDirty('status')) {
                // Status changed manually, no action needed
                return;
            }
            
            // Auto-update status based on active tenants
            $unit->updateStatusFromLeases();
        });
    }
}