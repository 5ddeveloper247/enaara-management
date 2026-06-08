<?php

namespace App\Http\Requests\Admin\BalanceTracker;

use Illuminate\Foundation\Http\FormRequest;

class BalanceTrackerAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via validatePermissions() in the controller
    }

    public function rules(): array
    {
        return [
            'employee_id'    => 'required|exists:employees,id',
            'leave_type'     => 'required|string',
            'increment_type' => 'required|in:add,subtract',
            'days'           => ['required', 'numeric', 'min:0.5', function ($attribute, $value, $fail) {
                if (abs(fmod(((float) $value) * 2, 1.0)) > 0.00001) {
                    $fail('Number of days must be in 0.5 increments (e.g., 1, 1.5, 2, 5).');
                }
            }],
            'reason'         => 'required|string|min:5|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required'     => 'Please select an employee.',
            'employee_id.exists'       => 'The selected employee does not exist.',
            'increment_type.required'  => 'Adjustment type is required.',
            'increment_type.in'        => 'Adjustment type must be either "add" or "subtract".',
            'leave_type.required'      => 'Please select a leave type.',
            'days.required'            => 'Number of days is required.',
            'days.min'                 => 'Minimum adjustment is 0.5 days.',
            'reason.required'          => 'A reason is mandatory for audit purposes.',
            'reason.max'               => 'Reason may not exceed 255 characters.',
        ];
    }
}
