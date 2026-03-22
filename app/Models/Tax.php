<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_jurisdiction_id',
        'bill_id',
        'bill_item_id',
        'taxable_amount',
        'tax_amount',
        'is_inclusive',
    ];

    protected $casts = [
        'taxable_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_inclusive' => 'boolean',
    ];

    public function taxJurisdiction()
    {
        return $this->belongsTo(TaxJurisdiction::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }
}