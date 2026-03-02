<?php

namespace App\Traits;

use App\Rules\NoHtmlTags;
use Illuminate\Support\Facades\Validator;

trait RoleTrait
{
    private function sanitizeStoreRoleData(&$inputs)
    {

        $validation = Validator::make($inputs, [
            'role_name'         => ['bail','required','string','max:255', new NoHtmlTags()]
        ]);

        if ($validation->fails()) {

            return $validation->errors()->first();

        }

        $inputs['role_name'] = sanitizeInput($inputs['role_name']);

        return null;

    }

    private function sanitizeUpdateRoleData(&$inputs)
    {

        $validation = Validator::make($inputs, [
            'role_name' => ['bail','required','string','max:255', new NoHtmlTags()],
            'access' => ['bail','nullable','array'],
        ]);

        if ($validation->fails()) {

            return $validation->errors()->first();

        }

        $inputs['role_name'] = sanitizeInput($inputs['role_name']);

        return null;

    }

}


