<?php

namespace App\Http\Requests\Admin\ShiftRoster;

use Illuminate\Foundation\Http\FormRequest;

class BulkShiftRosterFloorOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['required', 'string', 'regex:/^(employee|outsourced):\d+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Select at least one employee to load floor options.',
            'employee_ids.min' => 'Select at least one employee to load floor options.',
            'employee_ids.*.regex' => 'Employee selection format is invalid.',
        ];
    }
}
