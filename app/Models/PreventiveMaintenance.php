<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'maintenance_category_id',
        'title',
        'description',
        'frequency',
        'interval_days',
        'last_performed',
        'next_due_date',
        'status',
        'estimated_duration_hours',
        'estimated_cost',
        'checklist',
    ];

    protected $casts = [
        'last_performed' => 'date',
        'next_due_date' => 'date',
        'estimated_duration_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'checklist' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function maintenanceCategory()
    {
        return $this->belongsTo(MaintenanceCategory::class);
    }
}