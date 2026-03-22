<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloorPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'building_id',
        'floor_number',
        'total_area',
        'total_units',
        'layout_data',
        'image_path',
        'description',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'total_area' => 'decimal:2',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}