<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'utility_type_id',
        'period_start',
        'period_end',
        'consumption',
        'cost',
        'average_daily_consumption',
        'peak_consumption',
        'peak_date',
        'daily_breakdown',
        'is_estimated',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'peak_date' => 'date',
        'consumption' => 'decimal:4',
        'cost' => 'decimal:2',
        'average_daily_consumption' => 'decimal:4',
        'peak_consumption' => 'decimal:4',
        'daily_breakdown' => 'array',
        'is_estimated' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }
}