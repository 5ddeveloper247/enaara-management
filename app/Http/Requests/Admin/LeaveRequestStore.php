<?php

namespace App\Http\Requests\Admin;

use App\Models\Employee;
use App\Models\LeaveType;
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_half_day' => $this->boolean('is_half_day'),
        ]);
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => ['sometimes', 'boolean'],
            'half_day_session' => [
                'nullable',
                'required_if:is_half_day,true',
                Rule::in(['morning', 'afternoon']),
            ],
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
            'medical_report' => [
                Rule::requiredIf(fn () => $this->requiresSupportingDocument()),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('is_half_day')) {
                return;
            }

            if ($this->input('start_date') !== $this->input('end_date')) {
                $validator->errors()->add('end_date', 'End date must match start date for half-day leave.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'medical_report.required' => 'A supporting document is required for this leave type.',
            'medical_report.mimes' => 'Supporting document must be a PDF, JPG, JPEG, or PNG file.',
            'medical_report.max' => 'Supporting document must not exceed 5 MB.',
            'half_day_session.required_if' => 'Please select a session (morning or afternoon) for half-day leave.',
            'half_day_session.in' => 'Half-day session must be morning or afternoon.',
        ];
    }

    private function requiresSupportingDocument(): bool
    {
        $leaveTypeId = $this->input('leave_type_id');

        if (! $leaveTypeId) {
            return false;
        }

        return LeaveType::query()
            ->whereKey($leaveTypeId)
            ->where('leave_condition', 'conditional')
            ->exists();
    }
}
