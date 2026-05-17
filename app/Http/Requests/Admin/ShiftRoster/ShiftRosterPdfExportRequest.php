<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRosterPdfExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'employee_group' => ['required', 'in:internal,third_party'],
            'include_shift_times' => ['nullable', 'boolean'],
            'include_department_grouping' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid number.',
            'month.required' => 'Month is required.',
            'month.min' => 'Month must be between 1 and 12.',
            'month.max' => 'Month must be between 1 and 12.',
            'employee_group.required' => 'Employee group is required.',
            'employee_group.in' => 'Employee group is invalid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'year' => (int) $this->input('year'),
            'month' => (int) $this->input('month'),
            'employee_group' => $this->input('employee_group', 'internal'),
            'include_shift_times' => filter_var($this->input('include_shift_times'), FILTER_VALIDATE_BOOLEAN),
            'include_department_grouping' => filter_var(
                $this->input('include_department_grouping', true),
                FILTER_VALIDATE_BOOLEAN
            ),
            'include_deleted' => filter_var($this->input('include_deleted'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
