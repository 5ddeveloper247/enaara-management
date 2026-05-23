<?php

namespace App\Http\Requests\Admin;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequestStore extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = Employee::query()->find($this->input('employee_id'));
        $organizationId = $employee?->organization_id;
        $departmentId = $employee?->department_id;

        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id')
                    ->when($organizationId, fn($q) => $q->where(function ($qq) use ($organizationId) {
                        $qq->whereNull('organization_id')->orWhere('organization_id', $organizationId);
                    }))
                    ->when($departmentId, fn($q) => $q->where(function ($qq) use ($departmentId) {
                        $qq->whereNull('department_id')->orWhere('department_id', $departmentId);
                    })),
            ],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => [
                'required',
                'string',
                'max:600',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && preg_match('/<[^>]+>/', $value)) {
                        $fail('Reason must not contain HTML or script tags.');
                    }
                },
            ],
            // ✅ Medical report validation
            'medical_report' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                // 'max:2048', 
                // Rule::requiredIf(function () use ($duration) {
                //     return $this->isSickLeave() && $duration > 2;
                // }),
            ],
        ];
    }

    // private function isSickLeave(): bool
    // {
    //     $leaveTypeId = $this->input('leave_type_id');

    //     if (!$leaveTypeId) {
    //         return false;
    //     }

    //     $leaveType = \App\Models\LeaveType::find($leaveTypeId);

    //     return $leaveType && strtolower($leaveType->name) === 'sick leave';
    // }
}
