<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Employee;
use App\Models\OutsourcedEmployee;

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
            'employee_type' => ['required', 'in:employee,outsourced'],
            'employee_id' => [
                'required',
                'integer',
            ],
            'shift_planner_id' => ['required', 'integer', 'exists:shift_planners,id'],
            'roster_date' => ['required', 'date'],

            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
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
            'employee_type.required' => 'Employee type is required.',
            'employee_type.in' => 'Employee type is invalid.',
            'employee_id.required' => 'Employee is required.',
            'employee_id.integer' => 'Selected employee is invalid.',

            'shift_planner_id.required' => 'Shift is required.',
            'shift_planner_id.exists' => 'Selected shift does not exist.',

            'roster_date.required' => 'Roster date is required.',
            'roster_date.date' => 'Roster date must be a valid date.',

            'status.in' => 'Status must be either assigned or cancelled.',

            'notes.max' => 'Notes may not be greater than 1000 characters.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type = $this->input('employee_type');
            $id = (int) $this->input('employee_id');
            if (! $type || ! $id) {
                return;
            }

            if ($type === 'employee') {
                $exists = Employee::query()
                    ->whereKey($id)
                    ->where('engagement_mode', 'shifts')
                    ->exists();
                if (! $exists) {
                    $v->errors()->add('employee_id', 'Selected employee does not exist or is not shift-based.');
                }
                return;
            }

            $exists = OutsourcedEmployee::query()
                ->whereKey($id)
                ->whereNull('deleted_at')
                ->exists();
            if (! $exists) {
                $v->errors()->add('employee_id', 'Selected outsourced employee does not exist.');
            }
        });
    }

    /**
     * Prepare data before validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_type' => $this->input('employee_type', 'employee'),
            'status' => $this->has('status') ? $this->status : 1,
            'late_check_in' => $this->has('late_check_in') ? true : false,
        ]);
    }
}
