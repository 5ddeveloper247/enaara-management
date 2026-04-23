<?php

namespace App\Http\Requests\Admin\Employee;

use App\Models\ThirdParty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OutsourcedEmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) validatePermissions('admin/employees');
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('cnic_number')) {
            $this->merge([
                'cnic_number' => str_replace('-', '', (string) $this->input('cnic_number')),
            ]);
        }

        $phoneFields = ['mobile_number', 'supervisor_contact_number'];
        foreach ($phoneFields as $field) {
            if ($this->filled($field)) {
                $this->merge([
                    $field => preg_replace('/[^\d+]/', '', (string) $this->input($field)),
                ]);
            }
        }

        if (! $this->filled('biometric_id')) {
            $this->merge(['biometric_id' => null]);
        }

        $this->merge([
            'attendance_access' => $this->boolean('attendance_access') ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        $recordId = (int) ($this->route('id') ?? 0);

        return [
            'full_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{Zs}\.\-\'"]+$/u'],
            'cnic_number' => [
                'required',
                'string',
                'regex:/^[0-9]{13}$/',
                Rule::unique('outsourced_employees', 'cnic_number')
                    ->ignore($recordId)
                    ->whereNull('deleted_at'),
            ],
            'mobile_number' => ['required', 'string', 'regex:/^[0-9]{11,15}$/'],
            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],

            'contractor_company_id' => [
                'required',
                'integer',
                Rule::exists('third_parties', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'supervisor_name' => ['required', 'string', 'min:3', 'max:120', 'regex:/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{Zs}\.\-\'"]+$/u'],
            'supervisor_contact_number' => ['required', 'string', 'regex:/^[0-9]{11,15}$/'],

            'organization_id' => ['required', 'integer', Rule::exists('organizations', 'id')],
            'sbu_id' => [
                'required',
                'integer',
                Rule::exists('sbus', 'id')->where(function ($query) {
                    return $query->where('organization_id', (int) $this->input('organization_id'));
                }),
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(function ($query) {
                    return $query
                        ->where('organization_id', (int) $this->input('organization_id'))
                        ->where('sbu_id', (int) $this->input('sbu_id'));
                }),
            ],
            'job_role_trade' => ['required', 'string', 'min:2', 'max:150', 'regex:/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{N}\p{Zs}\.\-\'",&()\/#]+$/u'],
            'assigned_floor_ids' => ['nullable', 'array'],
            'assigned_floor_ids.*' => [
                'integer',
                Rule::exists('sbu_floors', 'id')->where(function ($query) {
                    return $query->where('sbu_id', (int) $this->input('sbu_id'));
                }),
            ],
            'date_of_deployment' => ['required', 'date'],

            'biometric_id' => [
                'nullable',
                'string',
                'min:3',
                'max:60',
                'regex:/^[A-Za-z0-9\-_\/]+$/',
                Rule::unique('outsourced_employees', 'biometric_id')
                    ->ignore($recordId)
                    ->whereNull('deleted_at'),
            ],
            'attendance_access' => ['required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $vendorId = (int) $this->input('contractor_company_id');
            $organizationId = (int) $this->input('organization_id');
            $sbuId = (int) $this->input('sbu_id');

            if ($vendorId > 0 && $organizationId > 0) {
                $vendorInOrganization = ThirdParty::query()
                    ->whereKey($vendorId)
                    ->whereHas('organizations', fn ($query) => $query->where('organizations.id', $organizationId))
                    ->exists();

                if (! $vendorInOrganization) {
                    $validator->errors()->add('contractor_company_id', 'Selected contractor company is not linked with the chosen organization.');
                }
            }

            if ($vendorId > 0 && $sbuId > 0) {
                $vendorInSbu = ThirdParty::query()
                    ->whereKey($vendorId)
                    ->whereHas('sbus', fn ($query) => $query->where('sbus.id', $sbuId))
                    ->exists();

                if (! $vendorInSbu) {
                    $validator->errors()->add('contractor_company_id', 'Selected contractor company is not linked with the chosen SBU.');
                }
            }

            $biometricId = (string) $this->input('biometric_id', '');
            if ($biometricId === '') {
                return;
            }

            if (! preg_match('/[A-Za-z]/', $biometricId)) {
                $validator->errors()->add('biometric_id', 'Biometric ID must include at least one alphabetic character.');
            }

            if (! preg_match('/[0-9]/', $biometricId)) {
                $validator->errors()->add('biometric_id', 'Biometric ID must include at least one digit.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Please enter full name.',
            'full_name.min' => 'Full name must be at least 3 characters.',
            'full_name.max' => 'Full name cannot exceed 120 characters.',
            'full_name.regex' => 'Full name must contain alphabetic text and cannot include script tags or invalid symbols.',

            'cnic_number.required' => 'Please enter CNIC number.',
            'cnic_number.regex' => 'CNIC must be exactly 13 digits (without spaces or extra characters).',
            'cnic_number.unique' => 'This CNIC is already registered in outsourced employees.',

            'mobile_number.required' => 'Please enter mobile number.',
            'mobile_number.regex' => 'Mobile number must be digits only and between 11 to 15 digits.',

            'photo.image' => 'Photo must be a valid image file.',
            'photo.mimes' => 'Photo must be in JPG, JPEG, PNG, or WEBP format.',
            'photo.max' => 'Photo size must not exceed 2 MB.',

            'contractor_company_id.required' => 'Please select contractor company.',
            'contractor_company_id.exists' => 'Selected contractor company is invalid.',

            'supervisor_name.required' => 'Please enter supervisor name.',
            'supervisor_name.min' => 'Supervisor name must be at least 3 characters.',
            'supervisor_name.max' => 'Supervisor name cannot exceed 120 characters.',
            'supervisor_name.regex' => 'Supervisor name must contain alphabetic text and cannot include script tags or invalid symbols.',

            'supervisor_contact_number.required' => 'Please enter supervisor contact number.',
            'supervisor_contact_number.regex' => 'Supervisor contact number must be digits only and between 11 to 15 digits.',

            'organization_id.required' => 'Please select organization.',
            'organization_id.exists' => 'Selected organization is invalid.',

            'sbu_id.required' => 'Please select SBU.',
            'sbu_id.exists' => 'Selected SBU is invalid for the chosen organization.',

            'department_id.required' => 'Please select department.',
            'department_id.exists' => 'Selected department is invalid for the chosen organization and SBU.',

            'job_role_trade.required' => 'Please enter job role / trade.',
            'job_role_trade.min' => 'Job role / trade must be at least 2 characters.',
            'job_role_trade.max' => 'Job role / trade cannot exceed 150 characters.',
            'job_role_trade.regex' => 'Job role / trade must contain valid text and cannot include script tags or invalid symbols.',

            'assigned_floor_ids.array' => 'Assigned floors must be an array.',
            'assigned_floor_ids.*.integer' => 'Invalid floor selection.',
            'assigned_floor_ids.*.exists' => 'Selected floor does not exist in the chosen SBU.',

            'date_of_deployment.required' => 'Please select date of deployment.',
            'date_of_deployment.date' => 'Date of deployment must be a valid date.',

            'biometric_id.min' => 'Biometric ID is too short. Minimum 3 characters are required.',
            'biometric_id.max' => 'Biometric ID is too long. Maximum 60 characters are allowed.',
            'biometric_id.regex' => 'Biometric ID contains invalid characters. Only letters, digits, "-", "_" and "/" are allowed.',
            'biometric_id.unique' => 'This biometric ID is already assigned to another outsourced employee.',

            'attendance_access.required' => 'Please select attendance access.',
            'attendance_access.boolean' => 'Attendance access value is invalid.',
        ];
    }
}

