<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_rule_id',
        'title',
        'message',
        'type',
        'severity',
        'data',
        'is_read',
        'read_at',
        'resolved_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function alertRule()
    {
        return $this->belongsTo(AlertRule::class);
    }

    public function alertable()
    {
        return $this->morphTo();
    }
}