<?php

namespace App\Http\Requests\Admin\RoleLevel;

use Illuminate\Foundation\Http\FormRequest;

class RoleLevelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:100|unique:role_levels,name,'.$id,
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ];
    }
}
