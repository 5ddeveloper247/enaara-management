<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\Admin\Employee\Concerns\ValidatesEmployeeRoleScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStepRequest extends FormRequest
{
    use ValidatesEmployeeRoleScope;

    protected function maxWordsRule(int $maxWords, string $fieldLabel): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($maxWords, $fieldLabel) {
            if ($value === null || trim((string) $value) === '') {
                return;
            }
            $wordCount = count(preg_split('/\s+/', trim((string) $value)));
            if ($wordCount > $maxWords) {
                $fail("{$fieldLabel} can be at most {$maxWords} words.");
            }
        };
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $step = (int) $this->input('step');
        $employeeId = $this->input('employee_id');

        // Base rules for ALL fields to enforce data types, max lengths, enums, etc.
        // We set most of them to 'nullable' by default so they only validate if present.
        $baseRules = [
            'step'                   => ['required', 'integer', 'min:1', 'max:6'],
            'employee_id'            => ['nullable', 'integer', 'exists:employees,id'],
            'full_name'              => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'father_name'            => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'email'                  => ['nullable', 'email', 'max:255'],
            'phone'                  => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'cnic'                   => ['nullable', 'string', 'max:20'],
            'cnic_expiry'            => ['nullable', 'date'],
            'father_cnic'            => ['nullable', 'string', 'max:20', 'regex:/^[0-9-]+$/'],
            'ntn'                    => ['nullable', 'string', 'max:50'],
            'gender'                 => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'nationality'            => ['nullable', 'string', 'max:100'],
            'dob'                    => ['nullable', 'date', 'before:today'],
            'domicile_district'      => ['nullable', 'string', 'max:100'],
            'domicile_province'      => ['nullable', 'string', 'max:100'],
            'city_of_birth'          => ['nullable', 'string', 'max:100'],
            'religion'               => ['nullable', 'string', 'max:100'],
            'sect'                   => ['nullable', 'string', 'max:100'],
            'marital_status'         => ['nullable', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
            'spouse_name'            => ['nullable', 'string', 'max:255'],
            'nok_name'               => ['nullable', 'string', 'max:255'],
            'nok_cnic'               => ['nullable', 'string', 'max:20'],
            'nok_relation'           => ['nullable', 'string', 'max:100'],
            'nok_dob'                => ['nullable', 'date'],
            'nok_contact'            => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'organization_id'        => ['nullable', 'integer', 'exists:organizations,id'],
            'sbu_id'                 => ['nullable', 'integer', 'exists:sbus,id'],
            'department_id'          => ['nullable', 'integer', 'exists:departments,id'],
            'role_id'                => ['nullable', 'integer', 'exists:roles,id'],
            'employee_type'          => ['nullable', 'string', 'max:100'],
            'employment_type'        => ['nullable', 'string', 'max:100'],
            'designation'            => ['nullable', 'string', 'max:255'],
            'grade'                  => ['nullable', 'string', 'max:50'],
            'branch'                 => ['nullable', 'string', 'max:100'],
            'location'               => ['nullable', 'string', 'max:255'],
            'vendor'                 => ['nullable', 'string', 'max:255'],
            'site'                   => ['nullable', 'string', 'max:255'],
            'join_date'              => ['nullable', 'date'],
            'floor_access'           => ['nullable', 'boolean'],
            'biometric_id'           => ['nullable', 'string', 'max:50'],
            'employment_category'    => ['nullable', Rule::in(['intern', 'contractual', 'engagement'])],
            'intern_type'            => ['nullable', Rule::in(['paid', 'unpaid'])],
            'intern_duration'        => ['nullable', 'string', 'max:100'],
            'contractual_type'       => ['nullable', Rule::in(['time_bound', 'open', 'project_based'])],
            'engagement_mode'        => ['nullable', Rule::in(['on_site', 'remote', 'shifts', 'hybrid'])],
            'hybrid_days'            => ['nullable', 'array'],
            'hybrid_days.*'          => ['nullable', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'sync_with_biometric'    => ['nullable', 'boolean'],
            'verification_status'    => ['nullable', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
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
            'residence_phone'        => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'emergency_contact'      => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'cell_no'                => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'contact_email'          => ['nullable', 'email', 'max:255'],
            'present_address'        => ['nullable', 'string', 'max:1000'],
            'permanent_address'      => ['nullable', 'string', 'max:1000'],
            'account_title'          => ['nullable', 'string', 'max:255'],
            'account_no'             => ['nullable', 'string', 'max:100', 'regex:/^[0-9]+$/'],
            'bank_branch'            => ['nullable', 'string', 'max:255'],
            'account_type'           => ['nullable', Rule::in(['Saving', 'Current'])],
            'family'                 => ['nullable', 'array'],
            'family.*.name'          => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'family.*.gender'        => ['nullable', Rule::in(['Male', 'Female'])],
            'family.*.dob'           => ['nullable', 'date'],
            'family.*.relation'      => ['nullable', 'string', 'max:100'],
            'family.*.occupation'    => ['nullable', 'string', 'max:255'],
            'academics'                  => ['nullable', 'array'],
            'academics.*.degree'         => ['nullable', 'string', $this->maxWordsRule(10, 'Certificate / degree')],
            'academics.*.grade_cgpa'     => ['nullable', 'string', $this->maxWordsRule(5, 'Grade / CGPA')],
            'academics.*.start_date'     => ['nullable', 'date'],
            'academics.*.end_date'       => ['nullable', 'date'],
            'academics.*.field_of_study' => ['nullable', 'string', 'max:80'],
            'academics.*.institute'      => ['nullable', 'string', $this->maxWordsRule(10, 'University / board / institute')],
            'employments'                      => ['nullable', 'array'],
            'employments.*.organization'       => ['nullable', 'string', 'max:255'],
            'employments.*.designation'        => ['nullable', 'string', 'max:255'],
            'employments.*.from_date'          => ['nullable', 'date'],
            'employments.*.to_date'            => ['nullable', 'date'],
            'employments.*.salary'             => ['nullable', 'string', 'max:100'],
            'employments.*.reason_for_leaving' => ['nullable', 'string', 'max:500'],
            'last_fitness_test'      => ['nullable', 'string', 'max:1000'],
            'has_disability'         => ['nullable', Rule::in(['yes', 'no'])],
            'blood_group'            => ['nullable', 'string', 'max:10'],
            'disability_type'        => ['nullable', 'string', 'max:100'],
            'disability_description' => ['nullable', 'string', 'max:1000'],
            'ref1_name'         => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'ref1_designation'  => ['nullable', 'string', 'max:255'],
            'ref1_organization' => ['nullable', 'string', 'max:255'],
            'ref1_contact'      => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'ref1_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],
            'ref2_name'         => ['nullable', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
            'ref2_designation'  => ['nullable', 'string', 'max:255'],
            'ref2_organization' => ['nullable', 'string', 'max:255'],
            'ref2_contact'      => ['nullable', 'string', 'max:15', 'regex:/^[0-9+\-\s()]+$/'],
            'ref2_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],
            'profile_photo'          => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png'],
            'kept_attachment_ids'    => ['nullable', 'array'],
            'kept_attachment_ids.*'  => ['integer', 'exists:media_files,id'],
            'attachments'            => ['nullable', 'array'],
            'attachments.*.name'     => ['nullable', 'string', 'max:255'],
            'attachments.*.type'     => ['nullable', 'string', 'max:100'],
            'attachments.*.description' => ['nullable', 'string', 'max:1000'],
            'attachments.*.files'    => ['nullable', 'array', 'min:1'],
            'attachments.*.files.*'  => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
            'create_user_account'    => ['nullable', 'boolean'],
            'password'               => ['nullable', 'string', 'min:8'],
        ];

        $stepRules = [];

        // STRICT rules for current step
        if ($step === 1) {
            $stepRules = [
                'full_name'      => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
                'cnic'           => ['required', 'string', 'max:20'],
                'cnic_expiry'    => ['required', 'date'],
                'dob'            => ['required', 'date', 'before:today'],
                'nationality'    => ['required', 'string', 'max:100'],
                'marital_status' => ['required', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
                'family.*.name'                  => ['required_with:family.*', 'string', 'max:255', 'regex:/[a-zA-Z]/'],
                'family.*.gender'                => ['required_with:family.*', Rule::in(['Male', 'Female'])],
                'family.*.dob'                   => ['required_with:family.*', 'date'],
                'family.*.relation'              => ['required_with:family.*', 'string', 'max:100'],
                'academics.*.degree'             => ['required_with:academics.*', 'string', $this->maxWordsRule(10, 'Certificate / degree')],
                'academics.*.grade_cgpa'         => ['required_with:academics.*', 'string', $this->maxWordsRule(5, 'Grade / CGPA')],
                'academics.*.start_date'         => ['required_with:academics.*', 'date'],
                'academics.*.end_date'           => ['required_with:academics.*', 'date'],
                'employments.*.organization'     => ['required_with:employments.*', 'string', 'max:255'],
                'employments.*.designation'      => ['required_with:employments.*', 'string', 'max:255'],
                'employments.*.from_date'        => ['required_with:employments.*', 'date'],
                'employments.*.to_date'          => ['required_with:employments.*', 'date'],
            ];
        } elseif ($step === 2) {
            $stepRules = [
                'employment_category' => ['required', Rule::in(['intern', 'contractual', 'engagement'])],
                'organization_id'     => ['required', 'integer', 'exists:organizations,id'],
                'role_id'             => ['required', 'integer', 'exists:roles,id'],
                'sbu_id'              => ['nullable', 'integer', 'exists:sbus,id', Rule::requiredIf(! $this->orgLevelRoleSelected())],
                'department_id'       => ['nullable', 'integer', 'exists:departments,id', Rule::requiredIf(! $this->orgLevelRoleSelected())],
                'join_date'           => ['required', 'date'],
            ];
        } elseif ($step === 3) {
            $stepRules = [
                'verification_status'    => ['required', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
            ];
        } elseif ($step === 5) {
            $stepRules = [
                'account_title' => ['required', 'string', 'max:255'],
                'account_no'    => ['required', 'string', 'max:100'],
                'bank_branch'   => ['required', 'string', 'max:255'],
                'account_type'  => ['required', Rule::in(['Saving', 'Current'])],
            ];
        } elseif ($step === 6) {
            $stepRules = [
                'cell_no'       => ['required', 'string', 'max:15'],
                'contact_email' => ['required', 'email', 'max:255'],
                'present_address' => ['required', 'string', 'max:1000'],
                'permanent_address' => ['required', 'string', 'max:1000'],
                'password'      => ['nullable', 'string', 'min:8', 'required_if:create_user_account,1'],
            ];
        }

        // Attachments global rule enforcing
        $globalStrictRules = [
            'attachments.*.name'     => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.files'    => ['required_with:attachments', 'array', 'min:1'],
        ];

        return array_merge($baseRules, $stepRules, $globalStrictRules);
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
            'academics.*.field_of_study.max'        => 'Field of study can be at most 80 characters.',
            'employments.*.organization.required_with'  => 'Organization name is required.',
            'employments.*.designation.required_with'   => 'Designation is required.',
            'employments.*.from_date.required_with'     => 'From date is required.',
            'employments.*.to_date.required_with'       => 'To date is required.',
            'password.required_if'            => 'Password is required when creating a user account.',
            'password.min'                    => 'Password must be at least 8 characters.',
            'profile_photo.mimes'             => 'Profile photo must be JPG or PNG.',
            'profile_photo.max'               => 'Profile photo must be at most 5MB.',
            'attachments.*.files.*.mimes'     => 'Attachment files must be JPG, PNG, PDF, or Word documents.',
            'attachments.*.files.*.max'       => 'Attachment files must be at most 10MB.',
            'attachments.*.name.required_with'=> 'Attachment name is required.',
            'grade.max'                       => 'Grade cannot exceed 50 characters.',
            'designation.max'                 => 'Designation cannot exceed 255 characters.',
            'phone.regex'                     => 'Phone number can only contain digits and + - ( ) symbols.',
            'nok_contact.regex'               => 'NOK contact can only contain digits and + - ( ) symbols.',
            'residence_phone.regex'           => 'Residence phone can only contain digits and + - ( ) symbols.',
            'emergency_contact.regex'         => 'Emergency contact can only contain digits and + - ( ) symbols.',
            'cell_no.regex'                   => 'Cell number can only contain digits and + - ( ) symbols.',
            'ref1_contact.regex'              => 'Reference 1 contact can only contain digits and + - ( ) symbols.',
            'ref2_contact.regex'              => 'Reference 2 contact can only contain digits and + - ( ) symbols.',
            'father_cnic.regex'               => 'Father CNIC can only contain digits and hyphen (-).',
            'account_no.regex'                => 'Account number must contain digits only.',
        ];
    }
}
