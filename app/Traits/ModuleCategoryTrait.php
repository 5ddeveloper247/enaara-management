<?php

namespace App\Traits;

use App\Rules\NoHtmlTags;
use Illuminate\Support\Facades\Validator;

trait ModuleCategoryTrait
{
    private function sanitizeStoreModuleCategoryData(&$inputs)
    {

        $validation = Validator::make($inputs, [
            'category_name'         => ['bail','required','max:100', new NoHtmlTags()]
        ]);

        if ($validation->fails()) {

            return $validation->errors()->first();

        }

        $inputs['category_name'] = sanitizeInput($inputs['category_name'],'alphanumeric');

        return null;

    }

}


