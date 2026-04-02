<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'module_category_id',
        'module_name',
        'route',
        'show_in_menu',
        'css_class',
        'display_order',
    ];

    protected $casts = [
        'show_in_menu' => 'integer',
        'display_order' => 'integer',
    ];

    public function moduleCategory(): BelongsTo
    {
        return $this->belongsTo(ModuleCategory::class, 'module_category_id', 'ID');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_privileges', 'module_id', 'role_id')
            ->whereNull('role_privileges.deleted_at')
            ->withTimestamps();
    }

    public function rolePrivileges(): HasMany
    {
        return $this->hasMany(RolePrivilege::class);
    }
}
