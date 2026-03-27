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
            'employeeId'     => 'required|integer|exists:employees,id',
            'adjustmentType' => 'required|in:add,subtract',
            'leaveType'      => 'required|string|in:annual,sick,casual',
            'days'           => 'required|numeric|min:0.5',
            'reason'         => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employeeId.required'     => 'Please select an employee.',
            'employeeId.exists'       => 'The selected employee does not exist.',
            'adjustmentType.required' => 'Adjustment type is required.',
            'adjustmentType.in'       => 'Adjustment type must be either "add" or "subtract".',
            'leaveType.required'      => 'Please select a leave type.',
            'leaveType.in'            => 'Leave type must be Annual, Sick, or Casual.',
            'days.required'           => 'Number of days is required.',
            'days.min'                => 'Minimum adjustment is 0.5 days.',
            'reason.required'         => 'A reason is mandatory for audit purposes.',
            'reason.max'              => 'Reason may not exceed 1000 characters.',
        ];
    }
}
