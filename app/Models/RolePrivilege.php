<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePrivilege extends Model
{
    use SoftDeletes;

    protected $table = 'role_privileges';

    protected $fillable = [
        'role_id',
        'module_id',
    ];

    protected $casts = [
        'role_id' => 'integer',
        'module_id' => 'integer',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public static function hasPermission($roleId, $currentUri)
    {
        $currentUri = trim($currentUri, '/');
        $routeVariations = [
            $currentUri,
            '/' . $currentUri,
            'admin/' . $currentUri,
            'admin.' . str_replace('/', '.', $currentUri),
        ];
        
        if (strpos($currentUri, 'admin/') === 0) {
            $routeVariations[] = str_replace('admin/', '', $currentUri);
            $routeVariations[] = '/' . str_replace('admin/', '', $currentUri);
        }
        
        $row = RolePrivilege::join('roles', 'role_privileges.role_id', '=', 'roles.id')
            ->join('modules', 'role_privileges.module_id', '=', 'modules.id')
            ->where('role_privileges.role_id', $roleId)
            ->whereIn('modules.route', $routeVariations)
            ->whereNull('role_privileges.deleted_at')
            ->where('roles.is_active', true)
            ->whereNull('modules.deleted_at')
            ->first();
        return $row;
    }

    public static function drawLeftMenu($roleIds, $moduleCatId)
    {
        return RolePrivilege::select('modules.*')
            ->join('roles', 'role_privileges.role_id', '=', 'roles.id')
            ->join('modules', 'role_privileges.module_id', '=', 'modules.id')
            ->whereIn('role_privileges.role_id', $roleIds)
            ->where('modules.module_category_id', $moduleCatId)
            ->where('modules.show_in_menu', 1)
            ->whereNull('role_privileges.deleted_at')
            ->where('roles.is_active', true)
            ->whereNull('modules.deleted_at')
            ->orderBy('modules.display_order')
            ->distinct('modules.id')
            ->get();
    }

    public static function drawAllMenuModulesForCategory(int $moduleCatId)
    {
        return Module::query()
            ->where('module_category_id', $moduleCatId)
            ->where('show_in_menu', 1)
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->get();
    }
}
