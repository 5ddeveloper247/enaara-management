<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleLevel extends Model
{
    protected $table = 'role_levels';
    protected $fillable = [
        'name',
        'description',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
