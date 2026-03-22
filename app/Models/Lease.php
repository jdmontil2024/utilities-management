<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'lease_number',
        'start_date',
        'end_date',
        'move_in_date',
        'move_out_date',
        'monthly_rent',
        'security_deposit',
        'payment_due_day',
        'lease_status',  // Your column name
        'lease_type',
        'terms',
        'utilities_included',
        'lease_agreement_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'payment_due_day' => 'integer',
        'terms' => 'array',
        'utilities_included' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function building()
    {
        return $this->hasOneThrough(
            Building::class, 
            Unit::class, 
            'id', // Foreign key on units table
            'id', // Foreign key on buildings table
            'unit_id', // Local key on leases table
            'building_id' // Local key on units table
        );
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'active' => 'Active',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'terminated' => 'Terminated',
        ];

        return $labels[$this->lease_status] ?? ucfirst($this->lease_status);
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'success',
            'pending' => 'warning',
            'expired' => 'danger',
            'terminated' => 'secondary'
        ];
        
        return $colors[$this->lease_status] ?? 'secondary';
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return 0;
        }
        return max(0, now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false));
    }

    public function getDaysLeftAttribute(): int
    {
        return $this->days_remaining;
    }

    public function getFormattedMonthlyRentAttribute(): string
    {
        return '₱' . number_format($this->monthly_rent ?? 0, 2);
    }

    public function getFormattedSecurityDepositAttribute(): string
    {
        return '₱' . number_format($this->security_deposit ?? 0, 2);
    }

    public function getDurationAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return 'N/A';
        }
        
        $months = $this->start_date->diffInMonths($this->end_date);
        $years = floor($months / 12);
        $remainingMonths = $months % 12;
        
        if ($years > 0 && $remainingMonths > 0) {
            return "{$years} yr, {$remainingMonths} mo";
        } elseif ($years > 0) {
            return "{$years} " . ($years > 1 ? 'yrs' : 'yr');
        } elseif ($months > 0) {
            return "{$months} " . ($months > 1 ? 'mos' : 'mo');
        } else {
            return 'Less than 1 month';
        }
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->lease_status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->lease_status === 'expired' || 
               ($this->lease_status === 'active' && $this->end_date < now());
    }

    public function getIsExpiringSoonAttribute($days = 30)
    {
        return $this->lease_status === 'active' && 
               $this->days_remaining > 0 && 
               $this->days_remaining <= $days;
    }

    public function getLeaseNumberAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Generate a default lease number if none exists
        return 'LSE-' . str_pad($this->id ?? 0, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('lease_status', 'active')
                     ->whereDate('start_date', '<=', now())
                     ->whereDate('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('lease_status', 'expired')
            ->orWhere(function($q) {
                $q->where('lease_status', 'active')
                  ->whereDate('end_date', '<', now());
            });
    }

    public function scopePending($query)
    {
        return $query->where('lease_status', 'pending');
    }

    public function scopeTerminated($query)
    {
        return $query->where('lease_status', 'terminated');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('lease_status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays($days));
    }

    public function scopeForBuilding($query, $buildingId)
    {
        return $query->whereHas('unit', function($q) use ($buildingId) {
            $q->where('building_id', $buildingId);
        });
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeStartingBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    public function scopeEndingBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('end_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('lease_number', 'like', "%{$search}%")
              ->orWhereHas('tenant', function($tenantQuery) use ($search) {
                  $tenantQuery->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
              })
              ->orWhereHas('unit', function($unitQuery) use ($search) {
                  $unitQuery->where('unit_number', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Helper Methods
     */
    public function terminate($moveOutDate = null)
    {
        $this->update([
            'lease_status' => 'terminated',
            'move_out_date' => $moveOutDate ?? now(),
            'end_date' => $moveOutDate ?? now(),
        ]);

        // Update unit status if this was the active lease
        if ($this->unit && $this->unit->status === 'occupied') {
            $hasOtherActiveLease = Lease::where('unit_id', $this->unit_id)
                ->where('id', '!=', $this->id)
                ->where('lease_status', 'active')
                ->exists();
            
            if (!$hasOtherActiveLease) {
                $this->unit->update(['status' => 'vacant']);
            }
        }
    }

    public function renew($newEndDate, $newMonthlyRent = null)
    {
        // Create new lease
        $newLease = $this->replicate();
        $newLease->lease_number = $this->generateLeaseNumber();
        $newLease->start_date = $this->end_date->addDay();
        $newLease->end_date = $newEndDate;
        $newLease->monthly_rent = $newMonthlyRent ?? $this->monthly_rent;
        $newLease->lease_status = 'active';
        $newLease->move_out_date = null;
        $newLease->save();

        // Mark current lease as expired
        $this->update(['lease_status' => 'expired']);

        return $newLease;
    }

 /**
 * Generate a building-based lease number with year tracking
 */
public static function generateLeaseNumber($buildingName, $buildingId, $startDate)
{
    // Generate abbreviation from building name
    $words = explode(' ', trim($buildingName));
    $abbrev = '';
    
    if (count($words) >= 2) {
        // For multi-word names: take first letter of each word
        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $abbrev .= strtoupper(substr(trim($word), 0, 1));
            }
        }
    } else {
        // For single word: take first 3 letters
        $cleanName = strtoupper(preg_replace('/[^A-Za-z]/', '', $buildingName));
        $abbrev = substr($cleanName, 0, 3);
    }
    
    // Add building ID for uniqueness (padded to 2 digits)
    $buildingCode = $abbrev . str_pad($buildingId, 2, '0', STR_PAD_LEFT);
    
    // Extract year from start date
    $year = \Carbon\Carbon::parse($startDate)->year;
    
    // Get the last lease for this building code and year
    $lastLease = self::withTrashed()
        ->where('lease_number', 'like', $buildingCode . '-' . $year . '-%')
        ->orderBy('lease_number', 'desc')
        ->first();
    
    if ($lastLease) {
        // Extract the sequential number (last 3 digits)
        $lastNumber = intval(substr($lastLease->lease_number, -3));
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }
    
    return $buildingCode . '-' . $year . '-' . $newNumber;
}
    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lease) {
            // Generate lease number if not provided
            if (!$lease->lease_number) {
                $lease->lease_number = self::generateLeaseNumber();
            }

            // Set move_in_date from start_date if not provided
            if (!$lease->move_in_date && $lease->start_date) {
                $lease->move_in_date = $lease->start_date;
            }

            // Set payment_due_day from start_date day if not provided
            if (!$lease->payment_due_day && $lease->start_date) {
                $lease->payment_due_day = $lease->start_date->day;
            }
        });

        static::created(function ($lease) {
            // Update unit status to occupied
            if ($lease->unit && $lease->lease_status === 'active') {
                $lease->unit->update(['status' => 'occupied']);
            }
        });

        static::updating(function ($lease) {
            // Handle unit status changes
            if ($lease->isDirty('lease_status') && $lease->unit) {
                if ($lease->lease_status !== 'active' && $lease->getOriginal('lease_status') === 'active') {
                    // Check if unit has any other active leases
                    $hasOtherActiveLease = Lease::where('unit_id', $lease->unit_id)
                        ->where('id', '!=', $lease->id)
                        ->where('lease_status', 'active')
                        ->exists();
                    
                    if (!$hasOtherActiveLease) {
                        $lease->unit->update(['status' => 'vacant']);
                    }
                } elseif ($lease->lease_status === 'active' && $lease->getOriginal('lease_status') !== 'active') {
                    $lease->unit->update(['status' => 'occupied']);
                }
            }
        });

        static::deleting(function ($lease) {
            // Free up the unit when lease is deleted
            if ($lease->unit && $lease->lease_status === 'active') {
                $hasOtherActiveLease = Lease::where('unit_id', $lease->unit_id)
                    ->where('id', '!=', $lease->id)
                    ->where('lease_status', 'active')
                    ->exists();
                
                if (!$hasOtherActiveLease) {
                    $lease->unit->update(['status' => 'vacant']);
                }
            }
        });
    }
}