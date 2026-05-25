<?php

namespace App\Traits;

use App\Models\Module;
use App\Models\Role;
use App\Models\RolePrivilege;
use App\Models\UserRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

trait HasPermissionsTrait
{
    protected static function resolveCurrentUserRoleIds(): array
    {
        $loggedUser = Auth::user();

        if (! $loggedUser) {
            return [];
        }

        return UserRole::where('user_id', $loggedUser->id)
            ->whereNotNull('role_id')
            ->whereNull('deleted_at')
            ->pluck('role_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function userHasSystemAdminRole(): bool
    {
        $roleIds = self::resolveCurrentUserRoleIds();

        if ($roleIds === []) {
            return false;
        }

        return Role::query()
            ->whereIn('id', $roleIds)
            ->where('is_active', true)
            ->where('is_system_admin', true)
            ->exists();
    }

    public function getModulesPremissions()
    {
        if (! Auth::user()) {
            return false;
        }

        if (self::userHasSystemAdminRole()) {
            return true;
        }

        $currentRoute = Route::getFacadeRoot()->current()->uri();
        $roleIdList = self::resolveCurrentUserRoleIds();

        if (! empty($roleIdList)) {
            foreach ($roleIdList as $roleId) {
                $permissionCheck = RolePrivilege::hasPermission(
                    $roleId,
                    $currentRoute
                );

                if ($permissionCheck) {
                    return $permissionCheck;
                }
            }
        }

        return false;
    }

    public static function getPremissionsByRoute($routeSlug)
    {
        if (! Auth::user()) {
            return false;
        }

        if (self::userHasSystemAdminRole()) {
            return true;
        }

        $routeName = trim((string) $routeSlug);
        $roleIdList = self::resolveCurrentUserRoleIds();

        if (! empty($roleIdList)) {
            foreach ($roleIdList as $roleId) {
                $permissionResult = RolePrivilege::hasPermission(
                    $roleId,
                    $routeName
                );

                if ($permissionResult) {
                    return $permissionResult;
                }
            }
        }

        return false;
    }

    public static function getLeftMenuByCategory($categoryId): Collection
    {
        if (! Auth::user()) {
            return collect();
        }

        if (self::userHasSystemAdminRole()) {
            return RolePrivilege::drawAllMenuModulesForCategory((int) $categoryId);
        }

        $roleIdList = self::resolveCurrentUserRoleIds();

        if (empty($roleIdList)) {
            return collect();
        }

        return RolePrivilege::drawLeftMenu($roleIdList, $categoryId);
    }
}
