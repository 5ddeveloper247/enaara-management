<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRosterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'shift_planner_id' => ['required', 'integer', 'exists:shift_planners,id'],
            'shift_type' => ['nullable', 'string', 'in:general,morning,evening,night'],
            'roster_date' => ['required', 'date'],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'floor' => ['nullable', 'string', 'max:255'],
            'late_check_in' => ['nullable', 'boolean'],

            'status' => ['nullable', 'integer', 'in:0,1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required.',
            'employee_id.exists' => 'Selected employee does not exist.',

            'shift_planner_id.required' => 'Shift is required.',
            'shift_planner_id.exists' => 'Selected shift does not exist.',

            'roster_date.required' => 'Roster date is required.',
            'roster_date.date' => 'Roster date must be a valid date.',

            'status.in' => 'Status must be either assigned or cancelled.',

            'notes.max' => 'Notes may not be greater than 1000 characters.',
        ];
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->has('status') ? $this->status : 1,
            'late_check_in' => $this->has('late_check_in') ? true : false,
        ]);
    }
}