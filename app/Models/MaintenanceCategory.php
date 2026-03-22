<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color_code',
        'sla_hours',
        'average_cost',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sla_hours' => 'integer',
        'average_cost' => 'decimal:2',
    ];

    /**
     * Get the maintenance requests for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get the preventive maintenances for this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function preventiveMaintenances()
    {
        return $this->hasMany(PreventiveMaintenance::class);
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query; // You can add active logic if needed
    }

    /**
     * Scope a query to only include categories with SLA.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSla($query)
    {
        return $query->whereNotNull('sla_hours');
    }

    /**
     * Get the formatted average cost.
     *
     * @return string
     */
    public function getFormattedAverageCostAttribute()
    {
        return '$' . number_format($this->average_cost, 2);
    }

    /**
     * Get the SLA in a readable format.
     *
     * @return string|null
     */
    public function getFormattedSlaAttribute()
    {
        if (!$this->sla_hours) {
            return null;
        }

        if ($this->sla_hours < 24) {
            return $this->sla_hours . ' hours';
        }

        $days = floor($this->sla_hours / 24);
        $hours = $this->sla_hours % 24;

        if ($hours === 0) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        }

        return $days . ' day' . ($days > 1 ? 's' : '') . ' ' . $hours . ' hours';
    }

    /**
     * Get the priority based on SLA hours.
     *
     * @return string
     */
    public function getPriorityAttribute()
    {
        if (!$this->sla_hours) {
            return 'normal';
        }

        if ($this->sla_hours <= 4) {
            return 'emergency';
        } elseif ($this->sla_hours <= 24) {
            return 'high';
        } elseif ($this->sla_hours <= 72) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}