<?php

namespace App\Http\Requests\Admin\ShiftPlanner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShiftPlannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $shiftId = $this->route('id'); // for update

        return [
            'name' => ['required', 'string', 'max:100'],

            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('shift_planners', 'code')->ignore($shiftId),
            ],

            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i'],

            'clock_in_window_minutes'  => ['required', 'integer', 'min:0', 'max:120'],
            'clock_out_window_minutes' => ['required', 'integer', 'min:0', 'max:120'],

            'grace_period_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'break_time_minutes'   => ['required', 'integer', 'min:0', 'max:180'],

            'overtime_allowed' => ['required', 'boolean'],

            'overtime_trigger_hours' => [
                'nullable',
                'numeric',
                'min:0',
                'max:24',
                // required only if overtime is enabled
                'required_if:overtime_allowed,1'
            ],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
