<?php

namespace App\Http\Requests\Admin\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->filled('name') ? preg_replace('/\s+/', ' ', trim((string) $this->input('name'))) : $this->input('name'),
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : $this->input('code'),
            'description' => $this->filled('description') ? trim((string) $this->input('description')) : $this->input('description'),
            'is_active' => $this->has('is_active')
                ? filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN)
                : $this->input('is_active'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('id') ?? $this->id;

        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'sbu_id' => [
                'required',
                Rule::exists('sbus', 'id')->where(function ($query) {
                    return $query->where('organization_id', $this->input('organization_id'));
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments')
                    ->where(function ($query) {
                        return $query
                            ->where('organization_id', $this->organization_id)
                            ->where('sbu_id', $this->sbu_id);
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
                Rule::exists('departments', 'id')->where(function ($query) use ($departmentId) {
                    return $query
                        ->where('organization_id', $this->input('organization_id'))
                        ->where('sbu_id', $this->input('sbu_id'))
                        ->where('id', '!=', $departmentId);
                }),
                function ($attribute, $value, $fail) use ($departmentId) {
                    if ($value == $departmentId) {
                        $fail('A department cannot be its own parent.');
                    }
                },
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'working_start_time' => ['nullable', 'date_format:H:i'],
            'working_end_time' => ['nullable', 'date_format:H:i', 'after:working_start_time'],
            'opening_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
            'closing_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
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
            'name.unique' => 'This department name is already in use for this SBU.',
            'code.unique' => 'This department code is already in use for this organization.',
            'code.max' => 'The department code cannot exceed 10 characters.',
            'parent_department_id.exists' => 'Selected parent department is invalid.',
            'working_days.*.in' => 'Selected working day is invalid.',
            'working_start_time.date_format' => 'Working start time must be in HH:MM format.',
            'working_end_time.date_format' => 'Working end time must be in HH:MM format.',
            'working_end_time.after' => 'Working end time must be after start time.',
            'opening_grace_period.integer' => 'Opening grace period must be a valid number.',
            'opening_grace_period.min' => 'Opening grace period cannot be negative.',
            'opening_grace_period.max' => 'Opening grace period cannot exceed 600 minutes.',
            'closing_grace_period.integer' => 'Closing grace period must be a valid number.',
            'closing_grace_period.min' => 'Closing grace period cannot be negative.',
            'closing_grace_period.max' => 'Closing grace period cannot exceed 600 minutes.',
        ];
    }

}
