<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'item_type',
        'description',
        'quantity',
        'rate',
        'amount',
        'tax_amount',
        'utility_type_id',
        'meter_reading_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'rate' => 'decimal:4',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }
}