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
            if ($value === null || trim((string)$value) === '') {
                return;
            }

            $wordCount = count(preg_split('/\s+/', trim((string)$value)));

            if ($wordCount > $maxWords) {
                $fail("{$fieldLabel} can be at most {$maxWords} words.");
            }
        };
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->orgLevelRoleSelected()) {
            $this->merge([
                'sbu_id' => null,
                'department_id' => null,
            ]);
        }

        $cnicFields = ['cnic', 'father_cnic', 'nok_cnic', 'spouse_cnic'];

        foreach ($cnicFields as $field) {
            if ($this->filled($field)) {
                $this->merge([
                    $field => str_replace('-', '', (string)$this->input($field)),
                ]);
            }
        }

        $phoneFields = [
            'phone',
            'nok_contact',
            'residence_phone',
            'emergency_contact',
            'cell_no',
            'ref1_contact',
            'ref2_contact',
        ];

        foreach ($phoneFields as $field) {
            if ($this->filled($field)) {
                $value = preg_replace('/[^\d+]/', '', (string)$this->input($field));
                $this->merge([
                    $field => $value,
                ]);
            }
        }

        if ($this->filled('account_no')) {
            $this->merge([
                'account_no' => preg_replace('/\s+/', '', (string)$this->input('account_no')),
            ]);
        }

        if ($this->filled('email')) {
            $this->merge([
                'email' => strtolower(trim((string)$this->input('email'))),
            ]);
        }

        if ($this->filled('contact_email')) {
            $this->merge([
                'contact_email' => strtolower(trim((string)$this->input('contact_email'))),
            ]);
        }

        $trimFields = ['full_name', 'father_name', 'name', 'present_address', 'permanent_address', 'relation', 'occupation'];
        foreach ($trimFields as $tf) {
            if ($this->filled($tf)) {
                $this->merge([$tf => trim((string)$this->input($tf))]);
            }
        }
    }

    protected function nameRegex(): string
    {
        return "/^[A-Za-z]+(?:[A-Za-z\s\.\-'_]*[A-Za-z])?$/";
    }

    protected function alphaTextRegex(): string
    {
        return "/^[A-Za-z]+[\sA-Za-z\.\-&,\/()']*$/";
    }

    protected function alphaNumericTextRegex(): string
    {
        return "/^[A-Za-z0-9]+[\sA-Za-z0-9\.\-&,\/()#']*$/";
    }

    protected function cnicRegex(): string
    {
        return '/^[0-9]{13,15}$/';
    }

    protected function contactRegex(): string
    {
        return '/^\+?[0-9]{10,15}$/';
    }

    protected function phoneRegex(): string
    {
        return '/^[0-9]{10,12}$/';
    }

    protected function alphanumericCodeRegex(): string
    {
        return '/^[A-Za-z0-9\/\-_]+$/';
    }

    public function rules(): array
    {
        $step = (int)$this->input('step');
        $employeeId = (int)$this->input('employee_id') ?: null;
        $subsection = $this->input('subsection');

        if ($subsection) {
            return $this->getSubsectionRules($subsection, $employeeId);
        }

        $baseRules = [
            'step' => ['required', 'integer', 'min:1', 'max:6'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],

            // Section A - General
            'full_name' => ['nullable', 'string', 'min:3', 'max:50', 'regex:' . $this->nameRegex()],
            'father_name' => ['nullable', 'string', 'min:3', 'max:50', 'regex:' . $this->nameRegex()],
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:50',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'phone' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'cnic' => [
                'bail',
                'nullable',
                'string',
                'regex:' . $this->cnicRegex(),
                'min:13',
                'max:15',
                Rule::unique('employees', 'cnic')->ignore($employeeId),
            ],
            'cnic_expiry' => ['nullable', 'date', 'after:today'],
            'father_cnic' => ['bail', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
            'ntn' => ['nullable', 'string', 'regex:/^(?:[0-9]{7}|[0-9]{13})$/'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'nationality' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'dob' => ['nullable', 'date', 'before:today'],
            'domicile_district' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
            'domicile_province' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'city_of_birth' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
            'religion' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'sect' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'marital_status' => ['nullable', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
            'spouse_name' => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'spouse_cnic' => ['nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
            'spouse_nationality' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'nok_name' => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'nok_cnic' => ['bail', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
            'nok_cnic_expiry_date' => ['nullable', 'date', 'after:today'],
            'nok_relation' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'nok_dob' => ['nullable', 'date', 'before:today'],
            'nok_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],

            // Section B - Employment
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'sbu_id' => ['nullable', 'integer', 'exists:sbus,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],

            'employee_type' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'employment_type' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'designation' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'grade' => ['nullable', 'string', 'max:10', 'regex:' . $this->alphaNumericTextRegex()],
            'branch' => ['nullable', 'string', 'min:2', 'max:50', 'regex:' . $this->alphaTextRegex()],
            'location' => ['nullable', 'string', 'min:2', 'max:255'],
            'vendor' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'site' => ['nullable', 'string', 'min:2', 'max:255'],
            'join_date' => ['nullable', 'date', 'before_or_equal:today'],
            'floor_access' => ['nullable', 'boolean'],
            'biometric_id' => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-_]+$/'],

            'employment_category' => ['nullable', Rule::in(['intern', 'contractual', 'engagement'])],
            'intern_type' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'intern_duration' => ['nullable', 'string', 'max:100'],
            'contractual_type' => ['nullable', Rule::in(['time_bound', 'open', 'project_based'])],
            'engagement_mode' => ['nullable', Rule::in(['on_site', 'remote', 'shifts', 'hybrid'])],
            'hybrid_days' => ['nullable', 'array'],
            'hybrid_days.*' => ['nullable', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'sync_with_biometric' => ['nullable', 'boolean'],

            // Section C - Police Verification
            'verification_status' => ['nullable', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
            'msr_letter_no' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\/\-_]+$/'],
            'addressee' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
            'verifying_authority' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'verification_letter_no' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\/\-_]+$/'],
            'next_verification_date' => ['nullable', 'date', 'after_or_equal:today'],
            'police_remarks' => ['nullable', 'string', 'max:2000'],

            // Section D - Armed / Military
            'service_no' => ['nullable', 'string', 'max:50', 'regex:' . $this->alphanumericCodeRegex()],
            'rank' => ['nullable', 'string', 'min:1', 'max:20', 'regex:/^[A-Za-z0-9\s\.\-\/]+$/'],
            'medical_category' => ['nullable', 'string', 'min:1', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
            'date_of_commissioning' => ['nullable', 'date', 'before_or_equal:today'],
            'date_of_retirement' => ['nullable', 'date', 'after_or_equal:date_of_commissioning'],
            'reason_of_retirement' => ['nullable', 'string', 'min:3', 'max:255'],
            'corps_regiment' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'ex_army_unit' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'trade' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'pma_lc_ots' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],

            // Section E - Bank / Contact
            'residence_phone' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'emergency_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'cell_no' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'contact_email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'present_address' => ['nullable', 'string', 'min:10', 'max:1000'],
            'permanent_address' => ['nullable', 'string', 'min:10', 'max:1000'],

            'account_title' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->nameRegex()],
            'account_no' => ['nullable', 'string', 'min:8', 'max:24', 'regex:/^[0-9]+$/'],
            'bank_branch' => ['nullable', 'string', 'min:2', 'max:255'],
            'account_type' => ['nullable', Rule::in(['Saving', 'Current'])],

            // Family
            'family' => ['nullable', 'array'],
            'family.*.name' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
            'family.*.gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'family.*.dob' => ['nullable', 'date', 'before:today'],
            'family.*.relation' => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
            'family.*.occupation' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],

            // Academics
            'academics' => ['nullable', 'array'],
            'academics.*.degree' => ['nullable', 'string', 'max:255'],
            'academics.*.grade_cgpa' => ['nullable', 'string', 'max:50'],
            'academics.*.start_date' => ['nullable', 'date'],
            'academics.*.end_date' => ['nullable', 'date'],
            'academics.*.field_of_study' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
            'academics.*.institute' => ['nullable', 'string', 'max:255'],

            // Previous Employments
            'employments' => ['nullable', 'array'],
            'employments.*.organization' => ['nullable', 'string', 'min:2', 'max:100'],
            'employments.*.designation' => ['nullable', 'string', 'min:2', 'max:50'],
            'employments.*.from_date' => ['nullable', 'date'],
            'employments.*.to_date' => ['nullable', 'date'],
            'employments.*.salary' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'employments.*.reason_for_leaving' => ['nullable', 'string', 'max:500'],

            // Medical
            'last_fitness_test' => ['nullable', 'string', 'max:1000'],
            'has_disability' => ['nullable', Rule::in(['yes', 'no'])],
            'blood_group' => ['nullable', 'string', 'regex:/^(A|B|AB|O)[+-]$/'],
            'disability_type' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'disability_description' => ['nullable', 'string', 'max:1000'],

            // References
            'ref1_name' => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'ref1_designation' => ['nullable', 'string', 'min:2', 'max:255'],
            'ref1_organization' => ['nullable', 'string', 'min:2', 'max:255'],
            'ref1_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'ref1_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],

            'ref2_name' => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'ref2_designation' => ['nullable', 'string', 'min:2', 'max:255'],
            'ref2_organization' => ['nullable', 'string', 'min:2', 'max:255'],
            'ref2_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'ref2_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],

            // Attachments
            'profile_photo' => ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,gif,svg'],
            'kept_attachment_ids' => ['nullable', 'array'],
            'kept_attachment_ids.*' => ['integer', 'exists:media_files,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.type' => ['nullable', 'string', 'max:100'],
            'attachments.*.description' => ['nullable', 'string', 'max:1000'],
            'attachments.*.files' => ['required_with:attachments', 'array', 'min:1'],
            'attachments.*.files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],

            // Account
            'create_user_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:64', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ];

        $stepRules = [];

        if ($step === 1) {
            $stepRules = [
                'full_name' => ['required', 'string', 'min:3', 'max:50', 'regex:' . $this->nameRegex()],
                'cnic' => [
                    'bail',
                    'required',
                    'string',
                    'regex:' . $this->cnicRegex(),
                    'min:13',
                    'max:15',
                    Rule::unique('employees', 'cnic')->ignore($employeeId),
                ],
                'cnic_expiry' => ['required', 'date', 'after:today'],
                'dob' => ['required', 'date', 'before:today'],
                'nationality' => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
                'marital_status' => ['required', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
                'spouse_cnic' => [
                    'required_if:marital_status,Married', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'
                ],
                'spouse_nationality' => [
                    'required_if:marital_status,Married', 'nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()
                ],
                'nok_name' => ['required', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
                'nok_cnic' => ['bail', 'required', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
                'nok_cnic_expiry_date' => ['required', 'date', 'after:today'],
                'nok_relation' => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
                'nok_dob' => ['required', 'date', 'before:today'],
                'nok_contact' => ['required', 'string', 'regex:' . $this->contactRegex()],

                'family.*.name' => ['required_with:family.*', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
                'family.*.gender' => ['required_with:family.*', Rule::in(['Male', 'Female'])],
                'family.*.dob' => ['required_with:family.*', 'date', 'before:today'],
                'family.*.relation' => ['required_with:family.*', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],

                'academics.*.degree' => ['required_with:academics.*', 'string', 'max:255', $this->maxWordsRule(10, 'Certificate / Degree')],
                'academics.*.grade_cgpa' => ['required_with:academics.*', 'string', 'max:50', $this->maxWordsRule(5, 'Grade / CGPA')],
                'academics.*.start_date' => ['required_with:academics.*', 'date'],
                'academics.*.end_date' => ['required_with:academics.*', 'date'],

                'employments.*.organization' => ['required_with:employments.*', 'string', 'min:2', 'max:255'],
                'employments.*.designation' => ['required_with:employments.*', 'string', 'min:2', 'max:255'],
                'employments.*.from_date' => ['required_with:employments.*', 'date'],
                'employments.*.to_date' => ['required_with:employments.*', 'date'],
            ];
        }
        elseif ($step === 2) {
            $stepRules = [
                'employment_category' => ['required', Rule::in(['intern', 'contractual', 'engagement'])],
                'organization_id' => ['required', 'integer', 'exists:organizations,id'],
                'role_id' => ['required', 'integer', 'exists:roles,id'],
                'sbu_id' => [
                    'nullable',
                    'integer',
                    'exists:sbus,id',
                    Rule::requiredIf(!$this->orgLevelRoleSelected()),
                ],
                'department_id' => [
                    'nullable',
                    'integer',
                    'exists:departments,id',
                    Rule::requiredIf(!$this->orgLevelRoleSelected()),
                ],
                'join_date' => ['required', 'date', 'before_or_equal:today'],
                'intern_type' => [
                    'nullable',
                    Rule::in(['paid', 'unpaid']),
                    'required_if:employment_category,intern',
                ],
                'intern_duration' => [
                    'nullable',
                    'string',
                    'max:100',
                    'required_if:employment_category,intern',
                ],
                'contractual_type' => [
                    'nullable',
                    Rule::in(['time_bound', 'open', 'project_based']),
                    'required_if:employment_category,contractual',
                ],
                'engagement_mode' => [
                    'nullable',
                    Rule::in(['on_site', 'remote', 'shifts', 'hybrid']),
                    'required_if:employment_category,engagement',
                ],
                'hybrid_days' => [
                    'nullable',
                    'array',
                    Rule::requiredIf(fn() => $this->input('engagement_mode') === 'hybrid'),
                ],
            ];
        }
        elseif ($step === 3) {
            $stepRules = [
                'verification_status' => ['required', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
            ];
        }
        elseif ($step === 5) {
            $stepRules = [
                'account_title' => ['required', 'string', 'min:3', 'max:255', 'regex:' . $this->nameRegex()],
                'account_no' => ['required', 'string', 'min:8', 'max:24', 'regex:/^[0-9]+$/'],
                'bank_branch' => ['required', 'string', 'min:2', 'max:255'],
                'account_type' => ['required', Rule::in(['Saving', 'Current'])],
            ];
        }
        elseif ($step === 6) {
            $stepRules = [
                'cell_no' => ['required', 'string', 'regex:' . $this->contactRegex()],
                'contact_email' => [
                    'required',
                    'email:rfc',
                    'max:255',
                    Rule::unique('employee_contacts', 'email')->ignore($employeeId, 'employee_id'),
                ],
                'present_address' => ['required', 'string', 'min:10', 'max:1000'],
                'permanent_address' => ['required', 'string', 'min:10', 'max:1000'],
                'password' => [
                    'nullable',
                    'string',
                    'min:8',
                    'max:64',
                    'required_if:create_user_account,1',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                ],
            ];
        }

        return array_merge($baseRules, $stepRules);
    }

    protected function getSubsectionRules(string $subsection, $employeeId): array
    {
        $rules = [
            'employee_id' => ['required', 'integer', 'exists:employees,id,deleted_at,NULL'],
            'subsection'  => ['required', 'string'],
        ];

        switch ($subsection) {
            case 'photo':
                return array_merge($rules, [
                    'profile_photo' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,gif,svg'],
                ]);

            case 'attachment':
                return array_merge($rules, [
                    'attachments.*.name' => ['required', 'string', 'max:255'],
                    'attachments.*.type' => ['nullable', 'string', 'max:100'],
                    'attachments.*.description' => ['nullable', 'string', 'max:1000'],
                    'attachments.*.files' => ['required', 'array', 'min:1'],
                    'attachments.*.files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
                ]);

            case 'contact':
                return array_merge($rules, [
                    'cell_no'           => ['required', 'string', 'regex:' . $this->contactRegex()],
                    'contact_email'     => [
                        'required', 
                        'email:rfc', 
                        'max:255',
                        Rule::unique('employee_contacts', 'email')->ignore($employeeId, 'employee_id'),
                    ],
                    'present_address'   => ['required', 'string', 'min:10', 'max:1000'],
                    'permanent_address' => ['required', 'string', 'min:10', 'max:1000'],
                    'residence_phone'   => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                    'emergency_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                ]);

            case 'family_row':
                return array_merge($rules, [
                    'name'       => ['required', 'string', 'min:2', 'max:70', 'regex:' . $this->alphaNumericTextRegex()],
                    'gender'     => ['required', Rule::in(['Male', 'Female'])],
                    'dob'        => ['required', 'date', 'before:today'],
                    'relation'   => ['required', 'string', 'min:2', 'max:50', 'regex:' . $this->alphaNumericTextRegex()],
                    'occupation' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
                ]);

            case 'academic_row':
                return array_merge($rules, [
                    'degree'         => ['required', 'string', 'max:100', $this->maxWordsRule(20, 'Certificate / Degree')],
                    'grade_cgpa'     => ['required', 'string', 'max:50', $this->maxWordsRule(10, 'Grade / CGPA')],
                    'start_date'     => ['required', 'date'],
                    'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
                    'field_of_study' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
                    'institute'      => ['nullable', 'string', 'max:150', $this->maxWordsRule(20, 'University')],
                ]);

            case 'employment_row':
                return array_merge($rules, [
                    'organization'       => ['required', 'string', 'max:150'],
                    'designation'        => ['required', 'string', 'max:100'],
                    'from_date'          => ['required', 'date', 'before_or_equal:today'],
                    'to_date'            => ['required', 'date', 'after_or_equal:from_date'],
                    'salary'             => ['nullable', 'string', 'max:15'],
                    'reason_for_leaving' => ['nullable', 'string', 'max:200'],
                ]);

            case 'medical':
                return array_merge($rules, [
                    'last_fitness_test'      => ['nullable', 'string', 'max:500'],
                    'has_disability'         => ['required', Rule::in(['yes', 'no'])],
                    'blood_group'            => ['nullable', 'string', 'max:10', 'regex:' . $this->bloodGroupRegex()],
                    'disability_type'        => ['nullable', 'string', 'max:100'],
                    'disability_description' => ['nullable', 'string', 'max:1000'],
                ]);

            case 'references':
                return array_merge($rules, [
                    'ref1_name'         => ['nullable', 'string', 'max:50', 'regex:' . $this->nameRegex()],
                    'ref1_designation'  => ['nullable', 'string', 'max:50'],
                    'ref1_organization' => ['nullable', 'string', 'max:100'],
                    'ref1_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                    'ref1_relationship' => ['nullable', 'string', 'max:50'],
                    'ref2_name'         => ['nullable', 'string', 'max:50', 'regex:' . $this->nameRegex()],
                    'ref2_designation'  => ['nullable', 'string', 'max:50'],
                    'ref2_organization' => ['nullable', 'string', 'max:100'],
                    'ref2_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                    'ref2_relationship' => ['nullable', 'string', 'max:50'],
                ]);
        }

        return $rules;
    }

    protected function bloodGroupRegex(): string
    {
        return '/^(A|B|AB|O)[\+\-]$/';
    }

    public function messages(): array
    {
        return [
            // General
            'full_name.required' => 'Name is required.',
            'full_name.string' => 'Name must be a valid text value.',
            'full_name.min' => 'Name must be at least 3 characters.',
            'full_name.max' => 'Name must not exceed 50 characters.',
            'full_name.regex' => 'Name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            'father_name.string' => "Father name must be a valid text value.",
            'father_name.min' => "Father name must be at least 3 characters.",
            'cnic.required' => 'CNIC is required.',
            'cnic.regex' => 'CNIC must be in the standard format (XXXXX-XXXXXXX-X).',
            'cnic.unique' => 'This CNIC is already registered.',
            'spouse_cnic.required_if' => 'Spouse CNIC is required when status is Married.',
            'spouse_cnic.regex' => 'Spouse CNIC must be in the standard format (XXXXX-XXXXXXX-X).',
            'ntn.regex' => 'NTN must be either 7 digits or 13 digits.',

            // NOK
            'nok_name.required' => 'The Next of Kin (NOK) name is mandatory.',
            'nok_name.regex' => 'The Next of Kin (NOK) name may only contain letters and standard punctuation.',
            'nok_cnic.required' => 'The Next of Kin (NOK) CNIC is mandatory.',
            'nok_cnic.regex' => 'The Next of Kin (NOK) CNIC must be in a valid format (XXXXX-XXXXXXX-X).',
            'nok_cnic_expiry_date.required' => 'The Next of Kin (NOK) CNIC expiry date is mandatory.',
            'nok_cnic_expiry_date.after' => 'The Next of Kin (NOK) CNIC must not be expired.',
            'nok_relation.required' => 'The relationship with the Next of Kin (NOK) is mandatory.',
            'nok_relation.regex' => 'The Next of Kin (NOK) relation must contain text only.',
            'nok_dob.required' => 'The Next of Kin (NOK) date of birth is mandatory.',
            'nok_dob.before' => 'The Next of Kin (NOK) date of birth must be a past date.',
            'nok_contact.required' => 'The Next of Kin (NOK) contact number is mandatory.',
            'nok_contact.regex' => 'The Next of Kin (NOK) contact number must be a valid phone number.',

            'nationality.required' => 'Nationality is required.',
            'nationality.regex' => 'Nationality must contain text only.',

            'dob.required' => 'Date of birth is required.',
            'dob.date' => 'Date of birth must be a valid date.',
            'dob.before' => 'Date of birth must be before today.',

            'domicile_district.min' => 'Domicile district must be at least 2 characters.',
            'domicile_district.max' => 'Domicile district must not exceed 100 characters.',
            'domicile_district.regex' => 'Domicile district may contain letters, numbers, spaces, dots, hyphens, commas, slashes, parentheses, and apostrophes only.',

            'domicile_province.min' => 'Domicile province must be at least 2 characters.',
            'domicile_province.max' => 'Domicile province must not exceed 100 characters.',
            'domicile_province.regex' => 'Domicile province must contain text only.',

            'city_of_birth.min' => 'Town / City of birth must be at least 2 characters.',
            'city_of_birth.max' => 'Town / City of birth must not exceed 100 characters.',
            'city_of_birth.regex' => 'Town / City of birth may contain letters, numbers, spaces, dots, hyphens, commas, slashes, parentheses, and apostrophes only.',

            'religion.regex' => 'Religion must contain text only.',
            'sect.regex' => 'Sect must contain text only.',

            'marital_status.required' => 'Marital status is required.',

            'spouse_name.regex' => 'Spouse name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            // Employment
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization does not exist.',

            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU does not exist.',

            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'Selected department does not exist.',

            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'Selected role does not exist.',

            'employee_type.regex' => 'Employee type may only contain letters and standard punctuation.',
            'employment_type.regex' => 'Employment type may only contain letters and standard punctuation.',
            'designation.regex' => 'Designation may only contain letters, spaces, and punctuation (like dot or hyphen).',
            'grade.max' => 'The grade field must not exceed 10 characters.',
            'grade.regex' => 'Grade may only contain letters, numbers, spaces, and hyphens.',
            'branch.regex' => 'Branch may only contain letters, spaces, and standard punctuation.',
            'vendor.regex' => 'Vendor may only contain letters, spaces, and standard punctuation.',

            'join_date.required' => 'Date of joining is required.',
            'join_date.date' => 'Date of joining must be a valid date.',
            'join_date.before_or_equal' => 'Date of joining cannot be in the future.',

            'biometric_id.regex' => 'Biometric ID may only contain letters, numbers, hyphens, and underscores.',

            'employment_category.required' => 'Category is required.',
            'intern_type.required' => 'Intern type is required when category is Intern.',
            'intern_duration.required' => 'Intern duration is required when category is Intern.',
            'contractual_type.required' => 'Contractual type is required when category is Contractual.',
            'engagement_mode.required' => 'Engagement mode is required when category is Engagement.',
            'hybrid_days.required' => 'Hybrid days are required when engagement mode is Hybrid.',

            // Police Verification
            'verification_status.required' => 'Verification status is required.',
            'msr_letter_no.regex' => 'MSR letter number may only contain letters, numbers, slash, hyphen, and underscore.',
            'addressee.regex' => 'Addressee may contain letters, numbers, spaces, dots, hyphens, commas, slashes, parentheses, and apostrophes only.',
            'verifying_authority.regex' => 'Verifying authority must contain valid text only.',
            'verification_letter_no.regex' => 'Verification letter number may only contain letters, numbers, slash, hyphen, and underscore.',
            'next_verification_date.date' => 'Next verification date must be a valid date.',
            'next_verification_date.after_or_equal' => 'Next verification date must be today or a future date.',

            // Armed / Military
            'service_no.regex' => 'Service number may only contain letters, numbers, slash, hyphen, and underscore.',
            'rank.regex' => 'Rank may contain letters, numbers, spaces, dots, hyphens, and slashes only.',
            'medical_category.regex' => 'Medical category may only contain letters, numbers, spaces, and standard punctuation.',
            'date_of_commissioning.before_or_equal' => 'Date of commissioning / enrollment cannot be in the future.',
            'date_of_retirement.after_or_equal' => 'Date of retirement must be after or equal to date of commissioning / enrollment.',
            'corps_regiment.regex' => 'Corps / Regiment / Squadron must contain valid text only.',
            'ex_army_unit.regex' => 'Ex army unit must contain valid text only.',
            'trade.regex' => 'Trade must contain valid text only.',
            'pma_lc_ots.regex' => 'PMA/LC/OTS may only contain letters, numbers, spaces, and standard punctuation.',

            // Contact / Bank
            'residence_phone.regex' => 'Residence phone number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'emergency_contact.regex' => 'Emergency contact number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'cell_no.required' => 'Cell number is required.',
            'cell_no.regex' => 'Cell number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',

            'contact_email.required' => 'Contact email is required.',
            'contact_email.email'    => 'Please enter a valid contact email address.',
            'contact_email.unique'   => 'This email address is already registered to another employee.',

            'present_address.required' => 'Present address is required.',
            'present_address.min' => 'Present address must be at least 10 characters.',
            'permanent_address.required' => 'Permanent address is required.',
            'permanent_address.min' => 'Permanent address must be at least 10 characters.',

            'account_title.required' => 'Account title is required.',
            'account_title.regex' => 'Account title may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            'account_no.required' => 'Account number is required.',
            'account_no.min' => 'Account number must be at least 8 digits.',
            'account_no.max' => 'Account number must not exceed 24 digits.',
            'account_no.regex' => 'Account number must contain digits only.',

            'bank_branch.required' => 'Bank & branch is required.',
            'account_type.required' => 'Account type is required.',

            // Family
            'family.*.name.required_with' => 'Family member name is required.',
            'family.*.name.min' => 'Family member name must be at least 2 characters.',
            'family.*.name.max' => 'Family member name must not exceed 255 characters.',
            'family.*.name.regex' => 'Family member name contains invalid characters.',
            'family.*.gender.required_with' => 'Family member gender is required.',
            'family.*.dob.required_with' => 'Family member date of birth is required.',
            'family.*.dob.before' => 'Family member date of birth must be before today.',
            'family.*.relation.required_with' => 'Family member relation is required.',

            'family.*.occupation.regex' => 'Family member occupation contains invalid characters.',

            // Academics
            'academics.*.degree.required_with' => 'Degree / certificate is required.',
            'academics.*.degree.max' => 'Degree / certificate must not exceed 255 characters.',
            'academics.*.grade_cgpa.required_with' => 'Grade / CGPA is required.',
            'academics.*.grade_cgpa.max' => 'Grade / CGPA must not exceed 50 characters.',
            'academics.*.start_date.required_with' => 'Academic start date is required.',
            'academics.*.end_date.required_with' => 'Academic end date is required.',
            'academics.*.start_date.date' => 'Academic start date must be a valid date.',
            'academics.*.end_date.date' => 'Academic end date must be a valid date.',
            'academics.*.field_of_study.max' => 'Field of study must not exceed 255 characters.',
            'academics.*.field_of_study.regex' => 'Field of study contains invalid characters.',
            'academics.*.institute.max' => 'Institute name must not exceed 255 characters.',

            // Previous Employments
            'employments.*.organization.required_with' => 'Previous employment organization name is required.',
            'employments.*.organization.max' => 'Previous employment organization must not exceed 255 characters.',
            'employments.*.designation.required_with' => 'Previous employment designation is required.',
            'employments.*.designation.max' => 'Employment history row #:position: designation must not exceed 255 characters.',
            'employments.*.from_date.required_with' => 'Previous employment from date is required.',
            'employments.*.to_date.required_with' => 'Previous employment to date is required.',
            'employments.*.from_date.date' => 'Previous employment from date must be a valid date.',
            'employments.*.to_date.date' => 'Previous employment to date must be a valid date.',
            'employments.*.salary.numeric' => 'Salary must be a valid number.',
            'employments.*.salary.min' => 'Salary cannot be negative.',
            'employments.*.salary.max' => 'Salary is too large.',
            'employments.*.reason_for_leaving.max' => 'Reason for leaving must not exceed 1000 characters.',

            // Medical
            'blood_group.regex' => 'Blood group must be in a valid format like A+, O-, or AB+.',
            'disability_type.regex' => 'Disability type must contain valid text only.',

            // References
            'ref1_name.regex' => 'Reference 1 name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'ref1_contact.regex' => 'Reference 1 contact number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'ref2_name.regex' => 'Reference 2 name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'ref2_contact.regex' => 'Reference 2 contact number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',

            // Files
            'profile_photo.mimes' => 'Profile photo must be a JPG, JPEG, PNG, GIF, or SVG file.',
            'profile_photo.max' => 'Profile photo must not exceed 2 MB.',

            'attachments.*.name.required' => 'Attachment name is required.',
            'attachments.*.name.required_with' => 'Attachment name is required.',
            'attachments.*.name.string' => 'Attachment name must be a valid text string.',
            'attachments.*.name.max' => 'Attachment name must not exceed 255 characters.',
            'attachments.*.type.max' => 'Attachment type must not exceed 100 characters.',
            'attachments.*.description.max' => 'Attachment description must not exceed 1000 characters.',
            'attachments.*.files.required' => 'Please upload at least one valid file.',
            'attachments.*.files.required_with' => 'Please upload at least one valid file.',
            'attachments.*.files.array' => 'Uploaded files must be processed as an array.',
            'attachments.*.files.min' => 'Please upload at least one valid file.',
            'attachments.*.files.*.mimes' => 'Attachment file must be of type: jpg, jpeg, png, pdf, doc, or docx.',
            'attachments.*.files.*.max' => 'Each attachment file must not exceed 10 MB.',

            // Password
            'password.required_if' => 'Password is required when creating a user account.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 64 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ];
    }
}