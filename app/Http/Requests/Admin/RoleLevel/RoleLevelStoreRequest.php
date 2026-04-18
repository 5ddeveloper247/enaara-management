<?php

namespace App\Http\Requests\Admin\RoleLevel;

use Illuminate\Foundation\Http\FormRequest;

class RoleLevelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:role_levels,name',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1|unique:role_levels,level',
            'is_active' => 'boolean',
        ];
    }
}
