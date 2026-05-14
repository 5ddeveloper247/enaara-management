<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;

class ShiftRosterFloorOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_type' => ['required', 'in:employee,outsourced'],
            'employee_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_type.required' => 'Employee type is required.',
            'employee_type.in' => 'Employee type is invalid.',
            'employee_id.required' => 'Employee is required.',
            'employee_id.integer' => 'Selected employee is invalid.',
            'employee_id.min' => 'Selected employee is invalid.',
        ];
    }
}
