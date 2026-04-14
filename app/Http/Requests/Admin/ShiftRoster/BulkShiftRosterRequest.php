<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class BulkShiftRosterRequest extends FormRequest
{
    /**
     * Authorization
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $this->input('employee_ids', [])))));
            if ($ids === []) {
                return;
            }
            $validCount = Employee::query()
                ->whereKey($ids)
                ->where('engagement_mode', 'shifts')
                ->count();
            if ($validCount !== count($ids)) {
                $v->errors()->add(
                    'employee_ids',
                    'Only employees with shift-based work arrangement can be assigned on the roster.'
                );
            }
        });
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            // Employees
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'integer', 'exists:employees,id'],

            // Shift
            'shift_planner_id' => ['required', 'integer', 'exists:shift_planners,id'],

            // Dates
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['string'],
            'assign_mode' => ['required', 'string', 'in:default,custom'],

            // Options
            'check_conflicts' => ['nullable', 'boolean'],
            'override_existing' => ['nullable', 'boolean'],
            'exclude_weekends' => ['nullable', 'boolean'],

            // Optional
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Please select at least one employee.',
            'employee_ids.array' => 'Employees must be a valid array.',
            'employee_ids.min' => 'Select at least one employee.',
            'employee_ids.*.exists' => 'One or more selected employees are invalid.',

            'shift_planner_id.required' => 'Shift is required.',
            'shift_planner_id.exists' => 'Selected shift does not exist.',

            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be valid.',

            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be valid.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',

            'notes.max' => 'Notes must not exceed 1000 characters.',
        ];
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'check_conflicts' => filter_var($this->check_conflicts, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'override_existing' => filter_var($this->override_existing, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'exclude_weekends' => filter_var($this->exclude_weekends, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }
}