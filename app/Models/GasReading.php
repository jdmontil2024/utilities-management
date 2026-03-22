<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_reading_id',
        'unit_id',
        'consumption_ccf',
        'consumption_therms',
        'pressure_psi',
        'flow_rate_cfh',
        'temperature_f',
        'calorific_value_btu_per_cf',
        'gas_type',
        'appliance_usage',
        'has_leak_detection',
        'leak_detected',
        'carbon_monoxide_ppm',
        'methane_percentage',
        'cost',
        'delivery_charge',
        'storage_charge',
    ];

    protected $casts = [
        'consumption_ccf' => 'decimal:4',
        'consumption_therms' => 'decimal:4',
        'pressure_psi' => 'decimal:2',
        'flow_rate_cfh' => 'decimal:4',
        'temperature_f' => 'decimal:2',
        'calorific_value_btu_per_cf' => 'decimal:2',
        'appliance_usage' => 'array',
        'has_leak_detection' => 'boolean',
        'leak_detected' => 'boolean',
        'carbon_monoxide_ppm' => 'decimal:2',
        'methane_percentage' => 'decimal:2',
        'cost' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'storage_charge' => 'decimal:2',
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