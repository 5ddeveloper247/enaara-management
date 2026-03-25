<?php

namespace App\Http\Requests\Admin\Employe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeCode = $this->route('id');

        return [
            'full_name'            => 'required|string|max:255',
            'email'                => [
                'required',
                'email',
                Rule::unique('employees', 'email')->ignore($employeeCode, 'employee_code'),
            ],
            'phone'                => 'nullable|string|max:15',
            'employee_id'          => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_code')->ignore($employeeCode, 'employee_code'),
            ],
            'organization_id'      => 'nullable|exists:organizations,id',
            'sbu_id'               => 'nullable|exists:sbus,id',
            'department_id'        => 'nullable|exists:departments,id',
            'employee_type'        => 'nullable|string',
            'employment_type'      => 'nullable|string',
            'vendor'               => 'nullable|string|max:255',
            'site_assignment'      => 'nullable|string|max:255',
            'join_date'            => 'nullable|date',
            'floor_access_10'      => 'nullable|boolean',
            'biometric_id'         => 'nullable|string|max:50',
            'sync_with_biometric'  => 'nullable|boolean',
            'create_user_account'  => 'nullable|boolean',
            'user_role'            => 'nullable|string|required_if:create_user_account,1',
            'password'             => 'nullable|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'   => 'Employee full name is required.',
            'full_name.max'        => 'Full name must not exceed 255 characters.',
            'email.required'       => 'Email address is required.',
            'email.email'          => 'Please provide a valid email address.',
            'email.unique'         => 'This email address is already registered.',
            'phone.max'            => 'Phone number must not exceed 15 digits.',
            'employee_id.unique'   => 'This Employee ID is already in use.',
            'organization_id.exists' => 'Selected organization is invalid.',
            'sbu_id.exists'        => 'Selected SBU is invalid.',
            'department_id.exists' => 'Selected department is invalid.',
            'join_date.date'       => 'Please provide a valid joining date.',
            'user_role.required_if' => 'User role is required when creating a user account.',
            'password.min'         => 'Password must be at least 8 characters.',
        ];
    }
}
