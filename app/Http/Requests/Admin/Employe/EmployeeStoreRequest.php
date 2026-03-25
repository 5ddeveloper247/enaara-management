<?php

namespace App\Http\Requests\Admin\Employe;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assuming admin access is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employeeCode = $this->route('id'); // This is the employee_code from the URL

        return [
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('employees', 'email')->ignore($employeeCode, 'employee_code'),
            ],
            'phone' => 'nullable|string|max:20',
            'employee_id' => [
                'nullable',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('employees', 'employee_code')->ignore($employeeCode, 'employee_code'),
            ],
            'organization_id' => 'nullable|exists:organizations,id',
            'sbu_id' => 'nullable|exists:sbus,id',
            'department' => 'nullable|exists:departments,id',
            'employee_type' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'vendor' => 'nullable|string|max:255',
            'site_assignment' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'floor_access_10' => 'nullable|boolean',
            'biometric_id' => 'nullable|string|max:50',
            'sync_with_biometric' => 'nullable|boolean',
            'create_user_account' => 'nullable|boolean',
            'user_role' => 'nullable|string|required_if:create_user_account,1',
            'password' => 'nullable|string|min:8|required_if:passwordOption,set',
        ];
    }


}
