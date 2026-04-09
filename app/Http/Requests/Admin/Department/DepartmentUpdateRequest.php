<?php

namespace App\Http\Requests\Admin\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('id') ?? $this->id;

        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'sbu_id' => ['required', 'exists:sbus,id'],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments')
                    ->where(function ($query) {
                        return $query->where('organization_id', $this->organization_id);
                    })
                    ->ignore($departmentId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('departments')
                    ->where(function ($query) {
                        return $query->where('organization_id', $this->organization_id);
                    })
                    ->ignore($departmentId),
            ],
            'parent_department_id' => [
                'nullable',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($departmentId) {
                    if ($value == $departmentId) {
                        $fail('A department cannot be its own parent.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU is invalid.',
            'name.required' => 'Department name is required.',
            'name.unique' => 'This department name is already in use for this organization.',
            'code.unique' => 'This department code is already in use for this organization.',
            'code.max' => 'The department code cannot exceed 32 characters.',
            'parent_department_id.exists' => 'Selected parent department is invalid.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
