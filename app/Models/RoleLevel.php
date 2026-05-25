<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RoleLevel extends Model
{
    public const SYSTEM_ADMIN_LEVEL = 786;

    protected $table = 'role_levels';
    protected $fillable = [
        'name',
        'description',
        'level',
        'grade',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeExcludingSystemAdmin(Builder $query): Builder
    {
        $linkedIds = Role::query()
            ->where('is_system_admin', true)
            ->whereNotNull('role_level_id')
            ->pluck('role_level_id');

        return $query
            ->whereNotIn('id', $linkedIds)
            ->where('level', '!=', self::SYSTEM_ADMIN_LEVEL)
            ->whereRaw('LOWER(TRIM(COALESCE(name, ""))) != ?', ['super admin']);
    }
}
