<?php
namespace App\Traits;
use App\Models\Acl\ModuleModel;
use App\Models\Acl\RolePrivilgeModel;
use App\Models\Acl\RoleModel;
use App\Models\Acl\AdminUserRoleModel;
use Illuminate\Http\Request;
use Auth;
use Route;
trait HasPermissionsTrait {

  public function getModulesPremissions(){
    //echo Route::getFacadeRoot()->current()->uri(); exit;
    $return=false;
    $currentUri = Route::getFacadeRoot()->current()->uri();
    $adminUserId = Auth::guard('admin')->user()->id;
    $resultAdminRoles = AdminUserRoleModel::where('admin_ID',$adminUserId)->get();
    if($resultAdminRoles){
      foreach($resultAdminRoles as $rowAdminRole){
        $result = RolePrivilgeModel::hasPermission($rowAdminRole->role_ID,$currentUri);
        if($result)
          $return = $result;
      }
    }
    return $return;
  }

  public static function getModulesPremissionsBySlug($slug){
    // dd($slug);
    $return=false;
    $currentUri = trim($slug);
  
    $adminUserId = Auth::guard('admin')->user()->id;
    $resultAdminRoles = AdminUserRoleModel::where('admin_ID',$adminUserId)->get();
    if($resultAdminRoles){
      foreach($resultAdminRoles as $rowAdminRole){
        $result = RolePrivilgeModel::hasPermission($rowAdminRole->role_ID,$currentUri);
        if($result)
          $return = $result;
      }
    }
    return $return;
  }



}