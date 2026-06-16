<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use App\Services\ShiftRosterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ShiftRosterExcelExportRequest extends FormRequest
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
            'department_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Year is required.',
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
            'department_id' => $this->filled('department_id') ? (int) $this->input('department_id') : null,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            try {
                app(ShiftRosterService::class)->assertExportDepartmentInViewerScope(
                    $this->input('department_id') ? (int) $this->input('department_id') : null,
                    (string) $this->input('employee_group', 'internal')
                );
            } catch (ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $v->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
