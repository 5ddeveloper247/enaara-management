<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivity;

class Role extends Model
{
    use LogsActivity;
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'organization_id',
        'sbu_id',
        'department_id',
        'parent_role_id',
        'role_level_id',
        'is_active',
        'is_primary',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public static function slugFromName(string $name): string
    {
        return \Illuminate\Support\Str::slug($name);
    }

    public function rolePrivileges(): HasMany
    {
        return $this->hasMany(RolePrivilege::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'role_privileges', 'role_id', 'module_id')
            ->whereNull('role_privileges.deleted_at')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->whereNull('user_roles.deleted_at')
            ->withTimestamps();
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }
    
    public function parentRole()
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roleLevel()
    {
        return $this->belongsTo(RoleLevel::class, 'role_level_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function sbu()
    {
        return $this->belongsTo(Sbu::class);
    }

    public function sbus(): BelongsToMany
    {
        return $this->belongsToMany(Sbu::class, 'role_sbu', 'role_id', 'sbu_id')->withTimestamps();
    }

    public function isOrganizationLevelRole(): bool
    {
        if ($this->department_id !== null) {
            return false;
        }

        if ($this->sbu_id !== null) {
            return false;
        }

        if ($this->relationLoaded('sbus')) {
            return $this->sbus->isEmpty();
        }

        return ! $this->sbus()->exists();
    }

    /**
     * Numeric priority from role_levels: FK first, else match active role_levels.name to role name
     * (same idea as employee registration rolesData), for validation when role_level_id is not set.
     */
    public function resolvedNumericLevel(): ?int
    {
        $this->loadMissing('roleLevel:id,level');

        if ($this->roleLevel !== null) {
            $v = $this->roleLevel->level;
            if ($v !== null && $v !== '') {
                return (int) $v;
            }
        }

        $name = trim((string) ($this->name ?? ''));
        if ($name === '') {
            return null;
        }

        $min = RoleLevel::query()
            ->where('is_active', true)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($name)])
            ->min('level');

        return $min !== null ? (int) $min : null;
    }
}
