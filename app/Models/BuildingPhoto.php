<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildingPhoto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'building_id',
        'path',
        'filename',
        'original_name',
        'category',
        'description',
        'uploaded_by',
        'is_primary'
    ];
    
    protected $casts = [
        'is_primary' => 'boolean'
    ];
    
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}