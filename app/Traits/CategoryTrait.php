<?php

namespace App\Traits;

use App\Rules\NoHtmlTags;
use Illuminate\Support\Facades\Validator;

trait CategoryTrait
{
    private function sanitizeStoreCategoryData(&$inputs)
    {

        $validation = Validator::make($inputs, [
            'module_category_id'   => ['bail','required','integer','max:100', new NoHtmlTags()],
            'module_name'          => ['bail','required','string','max:100', new NoHtmlTags()],
            'route'                => ['bail','required','string','max:100', new NoHtmlTags()],
            'show_in_menu'         => ['bail','nullable','integer', new NoHtmlTags()],
            'css_class'            => ['bail','nullable','string', new NoHtmlTags()],
        ]);

        if ($validation->fails()) {

            return $validation->errors()->first();

        }

        $inputs['module_category_id'] = sanitizeInput($inputs['module_category_id'],'int');
        $inputs['module_name'] = sanitizeInput($inputs['module_name'],'alphanumeric');
        $inputs['route'] = sanitizeInput($inputs['route']);
        $inputs['show_in_menu'] = isset($inputs['show_in_menu']) ? sanitizeInput($inputs['show_in_menu'],'int') : 0;
        $inputs['css_class'] = sanitizeInput($inputs['css_class'] ?? '');

        return null;

    }

}


