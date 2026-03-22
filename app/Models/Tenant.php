<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Foreign keys
        'unit_id',
        'building_id',
        'user_id',
        
        // Personal Information
        'first_name',
        'last_name',
        'email',
        'phone',
        'alternate_phone',
        'date_of_birth',
        
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        
        // Identification
        'government_id',
        'id_type',
        
        // Employment
        'occupation',
        'employer',
        'annual_income',
        
        // Lease Information - Keeping for backward compatibility
        // These will be deprecated but kept for now
        'lease_start_date',
        'lease_end_date',
        'monthly_rent',
        'security_deposit',
        'lease_status',
        'lease_agreement_path',
        
        // Additional Occupants
        'number_of_occupants',
        'additional_occupants',
        
        // Notes & Status
        'notes',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'annual_income' => 'decimal:2',
        'is_active' => 'boolean',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'additional_occupants' => 'array',
        'number_of_occupants' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships - SIMPLE AND ROBUST
     */
    
    // Unit relationship - IMPORTANT for Unit Show page
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Building relationship - IMPORTANT for Building Show page
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    // All leases for this tenant
    public function leases()
    {
        return $this->hasMany(Lease::class)->latest('start_date');
    }

    /**
     * SIMPLE AND ROBUST: Current active lease
     * Returns the most recent active lease for this tenant
     * NO DATE FILTERING - if the user set it as active, it's active
     * This is what the Building Show page uses
     */
    public function currentLease()
    {
        return $this->hasOne(Lease::class)
                ->where('lease_status', 'active')
                ->latest('start_date');
    }

    /**
     * SIMPLE AND ROBUST: Any active lease (same as currentLease)
     * Kept for backward compatibility
     */
    public function activeLease()
    {
        return $this->hasOne(Lease::class)
                ->where('lease_status', 'active')
                ->latest('start_date');
    }

    /**
     * Currently occupied lease (with date validation)
     * Use this for unit occupancy status only
     */
    public function occupiedLease()
    {
        return $this->hasOne(Lease::class)
                ->where('lease_status', 'active')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->latest('start_date');
    }

    // Latest lease (regardless of status)
    public function latestLease()
    {
        return $this->hasOne(Lease::class)->latest('start_date');
    }

    // Expired leases
    public function expiredLeases()
    {
        return $this->hasMany(Lease::class)
                ->where('lease_status', 'expired');
    }

    // Payment methods
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    // Maintenance requests
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // User account relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper methods for safe date handling
     */
    
    // Get the best lease to display (for forms, views, etc.)
    public function getDisplayLeaseAttribute()
    {
        // First try to get current active lease (based on status only)
        if ($this->currentLease) {
            return $this->currentLease;
        }
        
        // Then try any active lease
        if ($this->activeLease) {
            return $this->activeLease;
        }
        
        // Finally, return the latest lease if any
        return $this->latestLease;
    }

    // Safely get lease start date (always returns Carbon or null)
    public function getSafeLeaseStartDateAttribute()
    {
        $displayLease = $this->display_lease;
        
        if ($displayLease && $displayLease->start_date) {
            return $displayLease->start_date;
        }
        
        // Fallback to stored value
        return $this->lease_start_date ? \Carbon\Carbon::parse($this->lease_start_date) : null;
    }

    // Safely get lease end date (always returns Carbon or null)
    public function getSafeLeaseEndDateAttribute()
    {
        $displayLease = $this->display_lease;
        
        if ($displayLease && $displayLease->end_date) {
            return $displayLease->end_date;
        }
        
        // Fallback to stored value
        return $this->lease_end_date ? \Carbon\Carbon::parse($this->lease_end_date) : null;
    }

    // Check if lease is active (based on status only)
    public function getHasActiveLeaseAttribute(): bool
    {
        return $this->activeLease !== null;
    }

    // Check if lease is currently occupied (started and not ended)
    public function getIsCurrentlyOccupiedAttribute(): bool
    {
        return $this->occupiedLease !== null;
    }

    /**
     * Accessors & Mutators
     */
    
    // Get full name
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Get initials for avatar
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name ?? '', 0, 1) . substr($this->last_name ?? '', 0, 1));
    }

    /**
     * Get lease status - SIMPLE AND ROBUST
     * If there's an active lease, it's active
     * Otherwise, check the latest lease
     */
    public function getLeaseStatusAttribute($value)
    {
        // If there's an active lease, status is active
        if ($this->activeLease) {
            return $this->activeLease->lease_status;
        }
        
        // Check if there's any lease at all
        if ($this->latestLease) {
            return $this->latestLease->lease_status;
        }
        
        // Fallback to stored value
        return $value ?? 'inactive';
    }

    // Get lease status label
    public function getLeaseStatusLabelAttribute(): string
    {
        $status = $this->lease_status;
        
        $labels = [
            'active' => 'Active',
            'expired' => 'Expired',
            'pending' => 'Pending',
            'terminated' => 'Terminated',
            'inactive' => 'No Active Lease'
        ];
        
        return $labels[$status] ?? ucfirst($status);
    }

    // Get lease status color class
    public function getLeaseStatusColorAttribute(): string
    {
        $status = $this->lease_status;
        
        $colors = [
            'active' => 'success',
            'expired' => 'danger',
            'pending' => 'warning',
            'terminated' => 'secondary',
            'inactive' => 'secondary'
        ];
        
        return $colors[$status] ?? 'secondary';
    }

    // Get monthly rent - FROM BEST AVAILABLE LEASE
    public function getMonthlyRentAttribute($value)
    {
        $displayLease = $this->display_lease;
        if ($displayLease) {
            return $displayLease->monthly_rent;
        }
        
        // Fallback to stored value
        return $value;
    }

    // Get security deposit - FROM BEST AVAILABLE LEASE
    public function getSecurityDepositAttribute($value)
    {
        $displayLease = $this->display_lease;
        if ($displayLease) {
            return $displayLease->security_deposit;
        }
        
        // Fallback to stored value
        return $value;
    }

    // Get days remaining on lease - FROM ACTIVE LEASE ONLY
    public function getLeaseDaysRemainingAttribute(): int
    {
        if (!$this->occupiedLease || !$this->occupiedLease->end_date) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->occupiedLease->end_date, false));
    }

    // Check if lease is expiring soon
    public function getIsLeaseExpiringSoonAttribute($days = 30): bool
    {
        if (!$this->occupiedLease || !$this->occupiedLease->end_date) {
            return false;
        }
        
        return $this->occupiedLease->end_date->isFuture() && 
               $this->occupiedLease->end_date->diffInDays(now()) <= $days;
    }

    // Get ID type label
    public function getIdTypeLabelAttribute(): string
    {
        $labels = [
            'passport' => 'Passport',
            'drivers_license' => "Driver's License",
            'national_id' => 'National ID',
            'sss' => 'SSS ID',
            'gsis' => 'GSIS ID',
            'postal_id' => 'Postal ID',
            'voters_id' => "Voter's ID",
            'prc_id' => 'PRC ID',
            'senior_citizen_id' => 'Senior Citizen ID',
            'pwd_id' => 'PWD ID',
            'other' => 'Other'
        ];
        
        return $labels[$this->id_type] ?? ucfirst($this->id_type);
    }

    // Get lease number from display lease
    public function getLeaseNumberAttribute(): ?string
    {
        return $this->display_lease?->lease_number;
    }

    // Get all lease history
    public function getLeaseHistoryAttribute()
    {
        return $this->leases()->with('unit.building')->get();
    }

    // Format monthly rent
    public function getFormattedMonthlyRentAttribute(): string
    {
        return '₱' . number_format($this->monthly_rent ?? 0, 2);
    }

    // Format security deposit
    public function getFormattedSecurityDepositAttribute(): string
    {
        return '₱' . number_format($this->security_deposit ?? 0, 2);
    }

    // Get formatted address
    public function getFullAddressAttribute(): string
    {
        if (!$this->building || !$this->unit) {
            return 'N/A';
        }
        
        return "{$this->building->name}, Unit {$this->unit->unit_number}";
    }

    /**
     * Helper Methods
     */
    
    // Check if lease is active
    public function isLeaseActive(): bool
    {
        return $this->has_active_lease;
    }

    // Check if currently occupied
    public function isCurrentlyOccupied(): bool
    {
        return $this->is_currently_occupied;
    }

    // Check if lease is expiring soon
    public function isLeaseExpiringSoon($days = 30): bool
    {
        return $this->is_lease_expiring_soon;
    }

    /**
     * Scopes
     */
    
    // Active tenants (with active lease by status only)
    public function scopeActive($query)
    {
        return $query->whereHas('activeLease');
    }

    // Tenants with any active lease (by status only)
    public function scopeWithActiveLease($query)
    {
        return $query->whereHas('activeLease');
    }

    // Currently occupied tenants (with date-valid active lease)
    public function scopeCurrentlyOccupied($query)
    {
        return $query->whereHas('occupiedLease');
    }

    // Inactive tenants (no active lease)
    public function scopeInactive($query)
    {
        return $query->whereDoesntHave('activeLease');
    }

    // Filter by building
    public function scopeInBuilding($query, $buildingId)
    {
        return $query->where('building_id', $buildingId);
    }

    // Filter by unit
    public function scopeInUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    // Filter by lease status
    public function scopeWithLeaseStatus($query, $status)
    {
        if ($status === 'active') {
            return $query->whereHas('activeLease');
        } elseif ($status === 'occupied') {
            return $query->whereHas('occupiedLease');
        } elseif ($status === 'inactive') {
            return $query->whereDoesntHave('leases');
        } else {
            return $query->whereHas('leases', function($q) use ($status) {
                $q->where('lease_status', $status);
            });
        }
    }

    // Tenants with expiring leases
    public function scopeWithExpiringLeases($query, $days = 30)
    {
        return $query->whereHas('occupiedLease', function($q) use ($days) {
            $q->whereDate('end_date', '>=', now())
              ->whereDate('end_date', '<=', now()->addDays($days));
        });
    }

    // Tenants with expired leases
    public function scopeWithExpiredLeases($query)
    {
        return $query->whereHas('leases', function($q) {
            $q->where('lease_status', 'expired');
        });
    }

    // Tenants with leases ending this month
    public function scopeWithLeasesEndingThisMonth($query)
    {
        return $query->whereHas('occupiedLease', function($q) {
            $q->whereMonth('end_date', now()->month)
              ->whereYear('end_date', now()->year);
        });
    }

    // Tenants with leases ending next month
    public function scopeWithLeasesEndingNextMonth($query)
    {
        $nextMonth = now()->addMonth();
        return $query->whereHas('occupiedLease', function($q) use ($nextMonth) {
            $q->whereMonth('end_date', $nextMonth->month)
              ->whereYear('end_date', $nextMonth->year);
        });
    }

    // Search tenants
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
        });
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a tenant, set building_id from unit if not provided
        static::creating(function ($tenant) {
            if (!$tenant->building_id && $tenant->unit_id) {
                $unit = Unit::find($tenant->unit_id);
                if ($unit) {
                    $tenant->building_id = $unit->building_id;
                }
            }
        });

        // When updating a tenant, update unit status if lease status changes
        static::updating(function ($tenant) {
            if ($tenant->isDirty('lease_status') && $tenant->unit_id) {
                $unit = Unit::find($tenant->unit_id);
                if ($unit) {
                    if ($tenant->lease_status === 'active') {
                        $unit->update(['status' => 'occupied']);
                    } else {
                        // Check if unit has any other active tenants
                        $hasActiveTenant = Tenant::where('unit_id', $unit->id)
                            ->where('lease_status', 'active')
                            ->where('id', '!=', $tenant->id)
                            ->exists();
                        
                        if (!$hasActiveTenant) {
                            $unit->update(['status' => 'vacant']);
                        }
                    }
                }
            }
        });

        // When deleting a tenant, free up the unit
        static::deleting(function ($tenant) {
            if ($tenant->unit_id && $tenant->lease_status === 'active') {
                $unit = Unit::find($tenant->unit_id);
                if ($unit) {
                    // Check if unit has any other active tenants
                    $hasActiveTenant = Tenant::where('unit_id', $unit->id)
                        ->where('lease_status', 'active')
                        ->where('id', '!=', $tenant->id)
                        ->exists();
                    
                    if (!$hasActiveTenant) {
                        $unit->update(['status' => 'vacant']);
                    }
                }
            }
        });
    }
}