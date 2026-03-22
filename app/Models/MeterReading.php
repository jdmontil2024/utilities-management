<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'utility_type_id',
        'current_reading',
        'previous_reading',
        'consumption',
        'reading_date',
        'reading_type',
        'reader_id',
        'notes',
        'meter_number',
        'is_billed',
    ];

    protected $casts = [
        'current_reading' => 'decimal:4',
        'previous_reading' => 'decimal:4',
        'consumption' => 'decimal:4',
        'reading_date' => 'date',
        'is_billed' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function reader()
    {
        return $this->belongsTo(User::class, 'reader_id');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class);
    }

    public function electricityReading()
    {
        return $this->hasOne(ElectricityReading::class);
    }

    public function waterReading()
    {
        return $this->hasOne(WaterReading::class);
    }

    public function gasReading()
    {
        return $this->hasOne(GasReading::class);
    }
}