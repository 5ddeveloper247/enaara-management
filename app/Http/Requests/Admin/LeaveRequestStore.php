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
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => [
                'required',
                Rule::exists('leave_types', 'id')
                    ->when($organizationId, fn ($q) => $q->where(function ($qq) use ($organizationId) {
                        $qq->whereNull('organization_id')->orWhere('organization_id', $organizationId);
                    }))
                    ->when($departmentId, fn ($q) => $q->where(function ($qq) use ($departmentId) {
                        $qq->whereNull('department_id')->orWhere('department_id', $departmentId);
                    })),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ];
    }
}
