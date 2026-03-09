<?php
namespace App\Traits;
use App\Models\Module;
use App\Models\RolePrivilege;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
trait HasPermissionsTrait {

  public function getModulesPremissions(){
    $return=false;
    $currentUri = Route::getFacadeRoot()->current()->uri();
    $user = Auth::user();
    if(!$user){
      return $return;
    }
    $adminUserId = $user->id;
    $resultAdminRoles = UserRole::where('user_id',$adminUserId)->get();
    if($resultAdminRoles){
      foreach($resultAdminRoles as $rowAdminRole){
        $result = RolePrivilege::hasPermission($rowAdminRole->role_id,$currentUri);
        if($result)
          $return = $result;
      }
    }
    return $return;
  }

  public static function getModulesPremissionsBySlug($slug){
    $return=false;
    $currentUri = trim($slug);
  
    $user = Auth::user();
    if(!$user){
      return $return;
    }
    $adminUserId = $user->id;
    $resultAdminRoles = UserRole::where('user_id',$adminUserId)->get();
    if($resultAdminRoles){
      foreach($resultAdminRoles as $rowAdminRole){
        $result = RolePrivilege::hasPermission($rowAdminRole->role_id,$currentUri);
        if($result)
          $return = $result;
      }
    }
    return $return;
  }

  public static function getLeftMenuByCategory($moduleCatId){
    $user = Auth::user();
    if(!$user){
      return collect();
    }
    $adminUserId = $user->id;
    $resultAdminRoles = UserRole::where('user_id',$adminUserId)->get();
    $roleIds = $resultAdminRoles->pluck('role_id')->toArray();
    if(empty($roleIds)){
      return collect();
    }
    return RolePrivilege::drawLeftMenu($roleIds, $moduleCatId);
  }

}