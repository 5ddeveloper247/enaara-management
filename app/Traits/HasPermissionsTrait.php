<?php
namespace App\Traits;
use App\Models\RolePrivilege;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
trait HasPermissionsTrait {

  public function getModulesPremissions(){
    $hasAccess = false;

    $currentRoute = Route::getFacadeRoot()->current()->uri();
    $loggedInUser = Auth::user();

    if (!$loggedInUser) {
        return $hasAccess;
    }

    $userId = $loggedInUser->id;

    $userRoles = UserRole::where('user_id', $userId)->get();

    if ($userRoles) {
        foreach ($userRoles as $roleItem) {

            $permissionCheck = RolePrivilege::hasPermission(
                $roleItem->role_id,
                $currentRoute
            );

            if ($permissionCheck) {
                $hasAccess = $permissionCheck;
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

    $userId = $currentUser->id;

    $assignedRoles = UserRole::where('user_id', $userId)->get();

    if ($assignedRoles) {
        foreach ($assignedRoles as $roleRecord) {

            $permissionResult = RolePrivilege::hasPermission(
                $roleRecord->role_id,
                $routeName
            );

            if ($permissionResult) {
                $permissionGranted = $permissionResult;
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

    $userId = $loggedUser->id;

    $userRoles = UserRole::where('user_id', $userId)->get();

    $roleIdList = $userRoles->pluck('role_id')->toArray();

    if (empty($roleIdList)) {
        return collect();
    }

    return RolePrivilege::drawLeftMenu($roleIdList, $categoryId);
  }

}