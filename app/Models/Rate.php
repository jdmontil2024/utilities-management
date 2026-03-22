<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_schedule_id',
        'min_consumption',
        'max_consumption',
        'rate_per_unit',
        'unit',
        'time_period',
        'season',
    ];

    protected $casts = [
        'min_consumption' => 'decimal:4',
        'max_consumption' => 'decimal:4',
        'rate_per_unit' => 'decimal:6',
    ];

    public function rateSchedule()
    {
        return $this->belongsTo(RateSchedule::class);
    }
}