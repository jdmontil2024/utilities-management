<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'tax_id',
        'service_type',
        'hourly_rate',
        'callout_fee',
        'payment_terms',
        'notes',
        'is_approved',
        'rating',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'callout_fee' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_vendor_id');
    }

    public function repairs()
    {
        return $this->hasMany(Repair::class);
    }
}