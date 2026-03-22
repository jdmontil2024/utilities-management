<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'tenant_id', // Keep for historical reference, but make nullable
        'maintenance_category_id',
        'title',
        'description',
        'priority',
        'status',
        'request_date',
        'scheduled_date',
        'completion_date',
        'assigned_vendor_id',
        'assigned_staff_id',
        'estimated_cost',
        'actual_cost',
        'resolution_notes',
        'tenant_rating',
        'tenant_feedback',
        'feedback_date',
        'access_instructions',
        'internal_notes',
        'assigned_by',
        'assigned_date',
    ];

    protected $casts = [
        'request_date' => 'date',
        'scheduled_date' => 'date',
        'completion_date' => 'date',
        'feedback_date' => 'date',
        'assigned_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'tenant_rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the unit that this maintenance request belongs to.
     * This is the primary relationship - requests stay with the unit forever.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tenant that requested this maintenance (historical reference only).
     * This is kept for audit purposes but does NOT affect unit associations.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the maintenance category.
     */
    public function maintenanceCategory()
    {
        return $this->belongsTo(MaintenanceCategory::class);
    }

    /**
     * Get the assigned vendor.
     */
    public function assignedVendor()
    {
        return $this->belongsTo(Vendor::class, 'assigned_vendor_id');
    }

    /**
     * Get the assigned staff member.
     */
    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Get the repairs for this maintenance request.
     */
    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }

    /**
     * Get the current tenant of the unit (if any) - for display purposes only.
     * This is a helper to show who is currently in the unit, not who requested.
     */
    public function getCurrentUnitTenantAttribute()
    {
        return $this->unit ? $this->unit->currentTenant : null;
    }

    /**
     * Get the name of the current tenant (for display).
     */
    public function getCurrentTenantNameAttribute()
    {
        $currentTenant = $this->getCurrentUnitTenantAttribute();
        return $currentTenant ? $currentTenant->full_name : 'No current tenant';
    }

    /**
     * Check if the request is overdue based on SLA.
     */
    public function isOverdue()
    {
        // If already completed or cancelled, not overdue
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        // If no category or no SLA hours, not overdue
        if (!$this->maintenanceCategory || !$this->maintenanceCategory->sla_hours) {
            return false;
        }

        $slaHours = $this->maintenanceCategory->sla_hours;
        $hoursSinceRequest = $this->request_date->diffInHours(now());

        return $hoursSinceRequest > $slaHours;
    }

    /**
     * Get the priority label.
     */
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'emergency' => 'Emergency',
        ];

        return $labels[$this->priority] ?? ucfirst($this->priority);
    }

    /**
     * Get the priority color class.
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'emergency' => 'dark',
        ];

        return $colors[$this->priority] ?? 'secondary';
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'submitted' => 'Submitted',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the status color class.
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'submitted' => 'secondary',
            'assigned' => 'primary',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope a query to only include open requests.
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope a query to only include overdue requests.
     */
    public function scopeOverdue($query)
    {
        return $query->open()
            ->whereHas('maintenanceCategory', function($q) {
                $q->whereNotNull('sla_hours');
            })
            ->get()
            ->filter(function($request) {
                return $request->isOverdue();
            });
    }

    /**
     * Scope a query to only include requests by priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include requests by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}