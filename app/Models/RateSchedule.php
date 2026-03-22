<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'utility_type_id',
        'effective_date',
        'expiration_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }
}