<?php

namespace App\Http\Requests\Admin\Employee;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->orgLevelRoleSelected()) {
            $this->merge([
                'sbu_id'         => null,
                'department_id'  => null,
            ]);
        }
    }

    protected function orgLevelRoleSelected(): bool
    {
        $roleId = $this->input('role_id');
        if (! $roleId) {
            return false;
        }
        $role = Role::query()->find($roleId);

        return $role && $role->department_id === null;
    }

    public function rules(): array
    {
        return [
            'full_name'              => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'father_name'            => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'email'                  => ['nullable', 'email', 'max:255', Rule::unique('employees', 'email')],
            'phone'                  => ['nullable', 'string', 'max:15'],
            'cnic'                   => ['required', 'string', 'max:20'],
            'cnic_expiry'            => ['required', 'date'],
            'father_cnic'            => ['nullable', 'string', 'max:20'],
            'ntn'                    => ['nullable', 'string', 'max:50'],
            'gender'                 => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'nationality'            => ['required', 'string', 'max:100'],
            'dob'                    => ['required', 'date', 'before:today'],
            'domicile_district'      => ['nullable', 'string', 'max:100'],
            'domicile_province'      => ['nullable', 'string', 'max:100'],
            'city_of_birth'          => ['nullable', 'string', 'max:100'],
            'religion'               => ['nullable', 'string', 'max:100'],
            'sect'                   => ['nullable', 'string', 'max:100'],
            'marital_status'         => ['required', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
            'spouse_name'            => ['nullable', 'string', 'max:255'],
            'nok_name'               => ['nullable', 'string', 'max:255'],
            'nok_cnic'               => ['nullable', 'string', 'max:20'],
            'nok_relation'           => ['nullable', 'string', 'max:100'],
            'nok_dob'                => ['nullable', 'date'],
            'nok_contact'            => ['nullable', 'string', 'max:15'],
            'organization_id'        => ['required', 'integer', 'exists:organizations,id'],
            'sbu_id'                 => ['required', 'integer', 'exists:sbus,id'],
            'department_id'          => ['required', 'integer', 'exists:departments,id'],
            'role_id'                => ['required', 'integer', 'exists:roles,id'],
            'employee_type'          => ['nullable', 'string', 'max:100'],
            'employment_type'        => ['nullable', 'string', 'max:100'],
            'designation'            => ['nullable', 'string', 'max:255'],
            'grade'                  => ['nullable', 'string', 'max:50'],
            'branch'                 => ['nullable', 'string', 'max:100'],
            'location'               => ['nullable', 'string', 'max:255'],
            'vendor'                 => ['nullable', 'string', 'max:255'],
            'site'                   => ['nullable', 'string', 'max:255'],
            'join_date'              => ['required', 'date'],
            'floor_access'           => ['nullable', 'boolean'],
            'biometric_id'           => ['nullable', 'string', 'max:50'],
            'employment_category'    => ['required', Rule::in(['intern', 'contractual', 'engagement'])],
            'intern_type'            => ['nullable', Rule::in(['paid', 'unpaid'])],
            'intern_duration'        => ['nullable', 'string', 'max:100'],
            'contractual_type'       => ['nullable', Rule::in(['time_bound', 'open', 'project_based'])],
            'engagement_mode'        => ['nullable', Rule::in(['on_site', 'remote', 'shifts', 'hybrid'])],
            'hybrid_days'            => ['nullable', 'array'],
            'hybrid_days.*'          => ['nullable', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'sync_with_biometric'    => ['nullable', 'boolean'],
            'verification_status'    => ['required', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
            'msr_letter_no'          => ['nullable', 'string', 'max:255'],
            'addressee'              => ['nullable', 'string', 'max:255'],
            'verifying_authority'    => ['nullable', 'string', 'max:255'],
            'verification_letter_no' => ['nullable', 'string', 'max:255'],
            'next_verification_date' => ['nullable', 'date'],
            'police_remarks'         => ['nullable', 'string', 'max:2000'],
            'service_no'             => ['nullable', 'string', 'max:100'],
            'rank'                   => ['nullable', 'string', 'max:100'],
            'medical_category'       => ['nullable', 'string', 'max:100'],
            'date_of_commissioning'  => ['nullable', 'date'],
            'date_of_retirement'     => ['nullable', 'date'],
            'reason_of_retirement'   => ['nullable', 'string', 'max:255'],
            'corps_regiment'         => ['nullable', 'string', 'max:255'],
            'ex_army_unit'           => ['nullable', 'string', 'max:255'],
            'trade'                  => ['nullable', 'string', 'max:100'],
            'pma_lc_ots'             => ['nullable', 'string', 'max:255'],
            'residence_phone'        => ['nullable', 'string', 'max:15'],
            'emergency_contact'      => ['nullable', 'string', 'max:15'],
            'cell_no'                => ['required', 'string', 'max:15'],
            'contact_email'          => ['required', 'email', 'max:255'],
            'present_address'        => ['required', 'string', 'max:1000'],
            'permanent_address'      => ['required', 'string', 'max:1000'],
            'account_title'          => ['required', 'string', 'max:255'],
            'account_no'             => ['required', 'string', 'max:100'],
            'bank_branch'            => ['required', 'string', 'max:255'],
            'account_type'           => ['required', Rule::in(['Saving', 'Current'])],
            'family'                         => ['nullable', 'array'],
            'family.*.name'                  => ['required_with:family.*', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'family.*.gender'                => ['required_with:family.*', Rule::in(['Male', 'Female'])],
            'family.*.dob'                   => ['required_with:family.*', 'date'],
            'family.*.relation'              => ['required_with:family.*', 'string', 'max:100'],
            'family.*.occupation'            => ['nullable', 'string', 'max:255'],
            'academics'                      => ['nullable', 'array'],
            'academics.*.degree'             => ['required_with:academics.*', 'string', 'max:255'],
            'academics.*.grade_cgpa'         => ['required_with:academics.*', 'string', 'max:50'],
            'academics.*.start_date'         => ['required_with:academics.*', 'date'],
            'academics.*.end_date'           => ['required_with:academics.*', 'date'],
            'academics.*.field_of_study'     => ['nullable', 'string', 'max:255'],
            'academics.*.institute'          => ['nullable', 'string', 'max:255'],
            'employments'                    => ['nullable', 'array'],
            'employments.*.organization'     => ['required_with:employments.*', 'string', 'max:255'],
            'employments.*.designation'      => ['required_with:employments.*', 'string', 'max:255'],
            'employments.*.from_date'        => ['required_with:employments.*', 'date'],
            'employments.*.to_date'          => ['required_with:employments.*', 'date'],
            'employments.*.salary'           => ['nullable', 'string', 'max:100'],
            'employments.*.reason_for_leaving' => ['nullable', 'string', 'max:500'],
            'last_fitness_test'      => ['nullable', 'string', 'max:1000'],
            'has_disability'         => ['nullable', Rule::in(['yes', 'no'])],
            'blood_group'            => ['nullable', 'string', 'max:10'],
            'disability_type'        => ['nullable', 'string', 'max:100'],
            'disability_description' => ['nullable', 'string', 'max:1000'],
            'ref1_name'         => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'ref1_designation'  => ['nullable', 'string', 'max:255'],
            'ref1_organization' => ['nullable', 'string', 'max:255'],
            'ref1_contact'      => ['nullable', 'string', 'max:15'],
            'ref1_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],
            'ref2_name'         => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'ref2_designation'  => ['nullable', 'string', 'max:255'],
            'ref2_organization' => ['nullable', 'string', 'max:255'],
            'ref2_contact'      => ['nullable', 'string', 'max:15'],
            'ref2_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],
            'profile_photo'          => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png'],
            'kept_attachment_ids'    => ['nullable', 'array'],
            'kept_attachment_ids.*'  => ['integer', 'exists:media_files,id'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*.name'     => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.type'     => ['nullable', 'string', 'max:100'],
            'attachments.*.description' => ['nullable', 'string', 'max:1000'],
            'attachments.*.files'    => ['required_with:attachments', 'array', 'min:1'],
            'attachments.*.files.*'  => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
            'create_user_account'    => ['nullable', 'boolean'],
            'password'               => ['nullable', 'string', 'min:8', 'required_if:create_user_account,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'              => 'Full name is required.',
            'full_name.regex'                 => 'Full name must contain at least one letter.',
            'father_name.regex'               => "Father's name must contain at least one letter.",
            'email.email'                     => 'Please enter a valid email address.',
            'email.unique'                    => 'This email is already registered.',
            'cnic.required'                   => 'CNIC is required.',
            'cnic_expiry.required'            => 'CNIC Expiry Date is required.',
            'nationality.required'            => 'Nationality is required.',
            'dob.required'                    => 'Date of Birth is required.',
            'dob.before'                      => 'Date of birth must be before today.',
            'marital_status.required'         => 'Marital Status is required.',
            'employment_category.required'    => 'Category is required.',
            'organization_id.required'        => 'Organization is required.',
            'organization_id.exists'          => 'Selected organization does not exist.',
            'sbu_id.required'                 => 'SBU is required.',
            'sbu_id.exists'                   => 'Selected SBU does not exist.',
            'department_id.required'          => 'Department is required.',
            'department_id.exists'            => 'Selected department does not exist.',
            'role_id.required'                => 'Role is required.',
            'join_date.required'              => 'Date of Joining is required.',
            'verification_status.required'    => 'Verification Status is required.',
            'cell_no.required'                => 'Cell Number is required.',
            'cell_no.max'                     => 'Cell number must be at most 15 digits.',
            'contact_email.required'          => 'Email is required.',
            'contact_email.email'             => 'Please enter a valid email address.',
            'present_address.required'        => 'Present Address is required.',
            'permanent_address.required'      => 'Permanent Address is required.',
            'account_title.required'          => 'Account Title is required.',
            'account_no.required'             => 'Account No is required.',
            'bank_branch.required'            => 'Bank & Branch is required.',
            'account_type.required'           => 'A/C Type is required.',
            'phone.max'                       => 'Phone number must be at most 15 digits.',
            'nok_contact.max'                 => 'NOK contact must be at most 15 digits.',
            'residence_phone.max'             => 'Residence phone must be at most 15 digits.',
            'emergency_contact.max'           => 'Emergency contact must be at most 15 digits.',
            'ref1_contact.max'                => 'Reference 1 contact must be at most 15 digits.',
            'ref2_contact.max'                => 'Reference 2 contact must be at most 15 digits.',
            'family.*.name.required_with'     => 'Family member name is required.',
            'family.*.name.regex'             => 'Family member name must contain letters.',
            'family.*.gender.required_with'   => 'Family member gender is required.',
            'family.*.dob.required_with'      => 'Family member date of birth is required.',
            'family.*.relation.required_with' => 'Family member relation is required.',
            'academics.*.degree.required_with'      => 'Degree name is required.',
            'academics.*.grade_cgpa.required_with'  => 'Grade / CGPA is required.',
            'academics.*.start_date.required_with'  => 'Academic start date is required.',
            'academics.*.end_date.required_with'    => 'Academic end date is required.',
            'employments.*.organization.required_with'  => 'Organization name is required.',
            'employments.*.designation.required_with'   => 'Designation is required.',
            'employments.*.from_date.required_with'     => 'From date is required.',
            'employments.*.to_date.required_with'       => 'To date is required.',
            'password.required_if'            => 'Password is required when creating a user account.',
            'password.min'                    => 'Password must be at least 8 characters.',
            'profile_photo.mimes'             => 'Profile photo must be JPG or PNG.',
            'profile_photo.max'               => 'Profile photo must be at most 5MB.',
        ];
    }
}
