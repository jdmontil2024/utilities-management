<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_reading_id',
        'unit_id',
        'cold_water_gallons',
        'hot_water_gallons',
        'total_water_gallons',
        'water_pressure_psi',
        'flow_rate_gpm',
        'temperature_f',
        'water_source',
        'water_quality',
        'sub_meter_readings',
        'has_leak_detection',
        'leak_detected',
        'leak_rate_gph',
        'sewage_gallons',
        'stormwater_gallons',
        'recycled_water_gallons',
        'cost',
        'sewer_charge',
        'stormwater_charge',
    ];

    protected $casts = [
        'cold_water_gallons' => 'decimal:4',
        'hot_water_gallons' => 'decimal:4',
        'total_water_gallons' => 'decimal:4',
        'water_pressure_psi' => 'decimal:2',
        'flow_rate_gpm' => 'decimal:4',
        'temperature_f' => 'decimal:2',
        'sub_meter_readings' => 'array',
        'has_leak_detection' => 'boolean',
        'leak_detected' => 'boolean',
        'leak_rate_gph' => 'decimal:4',
        'sewage_gallons' => 'decimal:4',
        'stormwater_gallons' => 'decimal:4',
        'recycled_water_gallons' => 'decimal:4',
        'cost' => 'decimal:2',
        'sewer_charge' => 'decimal:2',
        'stormwater_charge' => 'decimal:2',
    ];

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}