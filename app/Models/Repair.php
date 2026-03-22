<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'vendor_id',
        'repair_type',
        'diagnosis',
        'work_performed',
        'parts_used',
        'labor_hours',
        'labor_rate',
        'parts_cost',
        'total_cost',
        'warranty_period',
        'warranty_expires',
        'before_photos',
        'after_photos',
    ];

    protected $casts = [
        'labor_hours' => 'decimal:2',
        'labor_rate' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'warranty_expires' => 'date',
        'before_photos' => 'array',
        'after_photos' => 'array',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}