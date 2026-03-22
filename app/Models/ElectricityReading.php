<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_reading_id',
        'unit_id',
        'peak_consumption_kwh',
        'off_peak_consumption_kwh',
        'shoulder_consumption_kwh',
        'power_factor',
        'maximum_demand_kw',
        'time_of_peak_demand',
        'reactive_power_kvar',
        'apparent_power_kva',
        'voltage_readings',
        'current_readings',
        'frequency_hz',
        'meter_type',
        'tariff_type',
        'has_time_of_use',
        'sub_meter_readings',
        'consumption_kwh',
        'cost',
        'demand_charge',
    ];

    protected $casts = [
        'peak_consumption_kwh' => 'decimal:4',
        'off_peak_consumption_kwh' => 'decimal:4',
        'shoulder_consumption_kwh' => 'decimal:4',
        'power_factor' => 'decimal:3',
        'maximum_demand_kw' => 'decimal:4',
        'time_of_peak_demand' => 'datetime',
        'reactive_power_kvar' => 'decimal:4',
        'apparent_power_kva' => 'decimal:4',
        'voltage_readings' => 'array',
        'current_readings' => 'array',
        'frequency_hz' => 'decimal:2',
        'has_time_of_use' => 'boolean',
        'sub_meter_readings' => 'array',
        'consumption_kwh' => 'decimal:4',
        'cost' => 'decimal:2',
        'demand_charge' => 'decimal:2',
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