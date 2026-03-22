<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'meter_type',
        'is_billable',
        'color_code',
        'icon',
    ];

    protected $casts = [
        'is_billable' => 'boolean',
    ];

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function rateSchedules()
    {
        return $this->hasMany(RateSchedule::class);
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }

    public function consumptions()
    {
        return $this->hasMany(Consumption::class);
    }
}