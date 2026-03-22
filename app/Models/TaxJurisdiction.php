<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxJurisdiction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'jurisdiction_code',
        'rate',
        'is_active',
        'effective_date',
        'expiration_date',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'expiration_date' => 'date',
    ];

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }
}