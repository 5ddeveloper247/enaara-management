<?php
namespace App\Traits;

use App\Models\Acl\AdminUserModel;
use App\Models\Acl\RoleModel;
use App\Models\CountryModel;
use App\Rules\NoHtmlTags;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

trait AdminUserTrait{

    private function getDropdownsData(&$data)
    {

        $data['rolesResult'] = RoleModel::orderBy('role_name')->get();
    }

    private function sanitizeStore(&$inputs)
    {
        $validation = Validator::make($inputs, [
            'first_name'       => ['bail', 'required', 'string', 'max:100', new NoHtmlTags()],
            'last_name'       => ['bail', 'required', 'string', 'max:100', new NoHtmlTags()],
            'email_address'   => ['bail', 'required', 'email', 'max:40', 'unique:tbl_employees', new NoHtmlTags()],
            'user_name'       => ['bail', 'required', 'string', 'max:40', 'unique:tbl_employees', new NoHtmlTags()],
            'avatar'          => ['bail', 'nullable', 'file', 'mimes:png,jpg,jpeg', 'max:2048', new NoHtmlTags()], // Validate avatar if present
            'role'            => ['bail', 'nullable', 'array', 'exists:tbl_roles,ID,deleted_at,NULL', new NoHtmlTags()] // Validate roles if present
        ]);
        // Check validation fails or not
        if($validation->fails())
        {
            return $validation->errors()->first();
        }

        return null;
    }

}

