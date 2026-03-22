<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'type',
        'last_four',
        'card_type',
        'bank_name',
        'account_type',
        'expiration_date',
        'is_default',
        'billing_address',
        'is_active',
        'payment_token',
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'billing_address' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}