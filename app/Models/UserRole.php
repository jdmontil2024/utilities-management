<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    protected $table = 'user_role';
    
    protected $casts = [
        'scope' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}