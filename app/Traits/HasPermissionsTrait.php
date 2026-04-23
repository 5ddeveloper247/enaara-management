<?php
namespace App\Traits;
use App\Models\RolePrivilege;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
trait HasPermissionsTrait {

  protected static function resolveCurrentUserRoleIds(): array
  {
    $loggedUser = Auth::user();

    if (!$loggedUser) {
      return [];
    }

    $roleIds = UserRole::where('user_id', $loggedUser->id)
      ->whereNotNull('role_id')
      ->pluck('role_id')
      ->map(fn ($id) => (int) $id)
      ->unique()
      ->values()
      ->all();

    return $roleIds;
  }

  public function getModulesPremissions(){
    $hasAccess = false;

    $currentRoute = Route::getFacadeRoot()->current()->uri();
    $loggedInUser = Auth::user();

    if (!$loggedInUser) {
        return $hasAccess;
    }

    $roleIdList = self::resolveCurrentUserRoleIds();

    if (!empty($roleIdList)) {
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

    return $hasAccess;
  }

  public static function getPremissionsByRoute($routeSlug){
    $permissionGranted = false;
    $routeName = trim($routeSlug);

    $currentUser = Auth::user();

    if (!$currentUser) {
        return $permissionGranted;
    }

    $roleIdList = self::resolveCurrentUserRoleIds();

    if (!empty($roleIdList)) {
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

    return $permissionGranted;
  }

  public static function getLeftMenuByCategory($categoryId){
    $loggedUser = Auth::user();

    if (!$loggedUser) {
        return collect();
    }

    $roleIdList = self::resolveCurrentUserRoleIds();

    if (empty($roleIdList)) {
        return collect();
    }

    return RolePrivilege::drawLeftMenu($roleIdList, $categoryId);
  }

}
