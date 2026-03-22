<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'condition',
        'threshold_value',
        'threshold_field',
        'notification_method',
        'recipients',
        'severity',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:4',
        'recipients' => 'array',
        'is_active' => 'boolean',
    ];

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}