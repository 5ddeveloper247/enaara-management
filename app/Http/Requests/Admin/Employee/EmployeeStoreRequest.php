<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\Admin\Employee\Concerns\NormalizesBankRowsFromRequest;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesExactlyOneSalaryBank;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesEmployeeRoleScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmployeeStoreRequest extends FormRequest
{
    use NormalizesBankRowsFromRequest;
    use ValidatesExactlyOneSalaryBank;
    use ValidatesEmployeeRoleScope;

    public function authorize(): bool { return true; }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->has('banks')) {
                $this->assertAtLeastOneSalaryBank($v);
                $this->assertAtLeastOnePersonalBank($v);
                $this->assertAtLeastOneCompanyOperatedBank($v);
            }
        });
    }

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

    protected function prepareForValidation(): void
    {
        $role = $this->resolveRoleForOrgLevelCheck();
        Log::info('EmployeeStoreRequest employment scope', [
            'role_id'         => $this->input('role_id'),
            'role_slug'       => $role?->slug,
            'role_name'       => $role?->name,
            'role_department_id' => $role?->department_id,
            'org_level_role'  => $this->orgLevelRoleSelected(),
        ]);
        $rawDept = $this->input('department_ids', []);
        if (! is_array($rawDept)) {
            $rawDept = $rawDept !== null && $rawDept !== '' ? [$rawDept] : [];
        }
        $deptIds = array_values(array_unique(array_filter(array_map('intval', $rawDept))));
        $this->merge([
            'department_ids' => $deptIds,
            'department_id'  => $deptIds[0] ?? null,
        ]);

        $cnicFields = ['cnic', 'father_cnic', 'nok_cnic', 'spouse_cnic'];
        foreach ($cnicFields as $field) {
            if ($this->filled($field)) {
                $this->merge([
                    $field => str_replace('-', '', (string) $this->input($field)),
                ]);
            }
        }

        $phoneFields = ['phone', 'nok_contact', 'residence_phone', 'emergency_contact', 'cell_no', 'ref1_contact', 'ref2_contact'];
        foreach ($phoneFields as $field) {
            if ($this->filled($field)) {
                $value = preg_replace('/[^\d+]/', '', (string) $this->input($field));
                $this->merge([$field => $value]);
            }
        }

        if ($this->filled('account_no')) {
            $this->merge(['account_no' => preg_replace('/\s+/', '', (string) $this->input('account_no'))]);
        }

        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }

        if ($this->filled('contact_email')) {
            $this->merge(['contact_email' => strtolower(trim((string) $this->input('contact_email')))]);
        }

        if ($this->filled('ntn')) {
            $this->merge(['ntn' => preg_replace('/\D/', '', (string) $this->input('ntn'))]);
        }

        $legacyCat = $this->input('employment_category');
        if ($legacyCat === 'contractual') {
            $this->merge([
                'employment_category' => 'employee',
                'employment_type' => $this->filled('employment_type') ? $this->input('employment_type') : 'contractual',
            ]);
        } elseif ($legacyCat === 'engagement') {
            $this->merge([
                'employment_category' => 'employee',
                'employment_type' => $this->filled('employment_type') ? $this->input('employment_type') : 'permanent',
            ]);
        }

        if ($this->input('engagement_mode') === 'on_site') {
            $this->merge(['engagement_mode' => 'standard']);
        }

        if ($this->input('engagement_mode') === 'standard' && ! $this->filled('standard_schedule_mode')) {
            $this->merge(['standard_schedule_mode' => 'default']);
        }

        $this->merge(['is_ex_armed_force' => $this->boolean('is_ex_armed_force')]);

        if ($this->has('banks')) {
            $this->normalizeBankRowsFromRequest();
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

    protected function bankInstitutionNameRegex(): string
    {
        return "/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{N}\p{Zs}0-9\'\.\-&,\\/#&()]{2,255}$/u";
    }

    protected function localeNameTextRegex(): string
    {
        return "/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{N}\p{Zs}\'\.\-&,\\/&()]{2,100}$/u";
    }

    protected function localePersonNameRegex(): string
    {
        return "/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{Zs}\.\-'_]{3,100}$/u";
    }

    protected function localeAlphaLabelRegex(): string
    {
        return "/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{Zs}\'\.\-&,\\/&()]{2,100}$/u";
    }

    protected function localeAlphanumericLabelRegex(): string
    {
        return "/^(?!.*[<>])(?=.*\p{L})[\p{L}\p{M}\p{N}\p{Zs}\'\.\-&,\\/#&()]{2,100}$/u";
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

    protected function alphanumericCodeRegex(): string
    {
        return '/^[A-Za-z0-9\/\-_]+$/';
    }

    public function rules(): array
    {
        return [
            // Section A — General Information
            'full_name'              => ['required', 'string', 'min:3', 'max:50', 'regex:' . $this->localePersonNameRegex()],
            'father_name'            => ['nullable', 'string', 'min:3', 'max:50', 'regex:' . $this->localePersonNameRegex()],
            'email'                  => ['nullable', 'email:rfc,dns', 'max:50', Rule::unique('employees', 'email')],
            'phone'                  => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'cnic'                   => ['bail', 'required', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15', Rule::unique('employees', 'cnic')],
            'cnic_expiry'            => ['required', 'date', 'after:today'],
            'father_cnic'            => ['bail', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
            'ntn'                    => ['nullable', 'string', 'regex:/^(?:[0-9]{7}|[0-9]{13})$/'],
            'is_ex_armed_force'      => ['nullable', 'boolean'],
            'gender'                 => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'nationality'            => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->localeNameTextRegex()],
            'dob'                    => ['required', 'date', 'before:today'],
            'domicile_district'      => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphanumericLabelRegex()],
            'domicile_province'      => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeNameTextRegex()],
            'city_of_birth'          => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphanumericLabelRegex()],
            'religion'               => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'sect'                   => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
            'marital_status'         => ['required', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widowed'])],
            'spouse_name'            => ['required_if:marital_status,Married', 'nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->localePersonNameRegex()],
            'nok_name'               => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->localePersonNameRegex()],
            'nok_cnic'               => ['bail', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
            'nok_relation'           => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
            'nok_dob'                => ['nullable', 'date', 'before:today'],
            'nok_contact'            => ['nullable', 'string', 'regex:' . $this->contactRegex()],

            // Section B — Employment
            'organization_id'        => ['required', 'integer', 'exists:organizations,id'],
            'sbu_id'                 => ['nullable', 'integer', 'exists:sbus,id', Rule::requiredIf(fn () => ! $this->orgLevelRoleSelected())],
            'department_id'          => ['nullable', 'integer', 'exists:departments,id'],
            'department_ids'         => [
                Rule::requiredIf(fn () => $this->deptRequiredForRole()),
                'nullable',
                'array',
                Rule::when($this->deptRequiredForRole(), ['min:1']),
            ],
            'department_ids.*'       => [
                'integer',
                Rule::exists('departments', 'id')->where(function ($q) {
                    $sbuId = $this->input('sbu_id');
                    if ($sbuId) {
                        $q->where('sbu_id', (int) $sbuId);
                    }
                }),
            ],
            'role_id'                => ['required', 'integer', 'exists:roles,id'],
            'employee_type'          => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'employment_type'        => [
                'nullable',
                Rule::in(['permanent', 'contractual']),
                Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'),
            ],
            'designation'            => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'grade'                  => ['nullable', 'string', 'max:10', 'regex:' . $this->alphaNumericTextRegex()],
            'branch'                 => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'location'               => ['nullable', 'string', 'max:255'],
            'vendor'                 => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'site'                   => ['nullable', 'string', 'max:255'],
            'join_date'              => ['required', 'date'],
            'floor_access'           => ['nullable', 'boolean'],
            'biometric_id'           => ['nullable', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-_]+$/'],
            'employment_category'    => ['required', Rule::in(['intern', 'consultant', 'employee'])],
            'intern_type'            => ['nullable', Rule::in(['paid', 'unpaid']), 'required_if:employment_category,intern'],
            'intern_duration'        => ['nullable', 'string', 'max:100', 'required_if:employment_category,intern'],
            'contractual_type'       => [
                'nullable',
                Rule::in(['time_bound', 'open', 'project_based']),
                Rule::requiredIf(fn () => $this->input('employment_category') === 'employee' && $this->input('employment_type') === 'contractual'),
            ],
            'contract_start_date'    => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'
                    && $this->input('employment_type') === 'contractual'
                    && $this->input('contractual_type') === 'time_bound'),
            ],
            'contract_end_date'      => [
                'nullable',
                'date',
                'after_or_equal:contract_start_date',
                Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'
                    && $this->input('employment_type') === 'contractual'
                    && $this->input('contractual_type') === 'time_bound'),
            ],
            'engagement_mode'        => ['required', Rule::in(['standard', 'remote', 'shifts', 'hybrid'])],
            'hybrid_days'            => [
                'nullable',
                'array',
                Rule::requiredIf(fn () => $this->input('engagement_mode') === 'hybrid'),
                Rule::when($this->input('engagement_mode') === 'hybrid', ['min:1']),
            ],
            'hybrid_days.*'          => ['nullable', Rule::in(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])],
            'standard_schedule_mode' => [
                'nullable',
                Rule::in(['default', 'custom']),
                Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard'),
            ],
            'working_days'           => [
                'nullable',
                'array',
                Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard'
                    && $this->input('standard_schedule_mode') === 'custom'),
                Rule::when(
                    $this->input('engagement_mode') === 'standard' && $this->input('standard_schedule_mode') === 'custom',
                    ['min:1']
                ),
            ],
            'working_days.*'         => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'working_start_time'     => [
                'nullable',
                'date_format:H:i',
                Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard'
                    && $this->input('standard_schedule_mode') === 'custom'),
            ],
            'working_end_time'       => [
                'nullable',
                'date_format:H:i',
                'after:working_start_time',
                Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard'
                    && $this->input('standard_schedule_mode') === 'custom'),
            ],
            'opening_grace_period'   => ['nullable', 'integer', 'min:0', 'max:600'],
            'closing_grace_period'   => ['nullable', 'integer', 'min:0', 'max:600'],
            'sync_with_biometric'    => ['nullable', 'boolean'],

            // Section C — Verification & Ex-Forces
            'verification_status'    => ['required', Rule::in(['Cleared', 'Not Cleared', 'In Process'])],
            'msr_letter_no'          => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'string',
                'max:255',
                Rule::when(fn () => filled($this->input('msr_letter_no')), ['regex:' . $this->alphanumericCodeRegex()]),
            ],
            'addressee'              => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'string',
                'max:255',
                Rule::when(fn () => ($this->input('verification_status') ?? '') !== 'In Process', ['min:2']),
                Rule::when(fn () => filled($this->input('addressee')), ['regex:' . $this->alphaTextRegex()]),
            ],
            'verifying_authority'    => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'string',
                'max:255',
                Rule::when(fn () => ($this->input('verification_status') ?? '') !== 'In Process', ['min:2']),
                Rule::when(fn () => filled($this->input('verifying_authority')), ['regex:' . $this->alphaTextRegex()]),
            ],
            'verification_letter_no' => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'string',
                'max:100',
                Rule::when(fn () => filled($this->input('verification_letter_no')), ['regex:' . $this->alphanumericCodeRegex()]),
            ],
            'next_verification_date' => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'date',
                Rule::when(
                    fn () => ($this->input('verification_status') ?? '') !== 'In Process',
                    ['after_or_equal:today']
                ),
            ],
            'police_remarks'         => [
                'bail',
                Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== 'In Process'),
                'nullable',
                'string',
                'max:2000',
                Rule::when(
                    fn () => ($this->input('verification_status') ?? '') !== 'In Process',
                    ['min:2']
                ),
            ],
            'service_no'             => ['nullable', 'string', 'max:100', 'regex:' . $this->alphanumericCodeRegex()],
            'rank'                   => ['nullable', 'string', 'min:1', 'max:20', 'regex:/^[A-Za-z0-9\s\.\-\/]+$/'],
            'medical_category'       => ['nullable', 'string', 'min:1', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
            'date_of_commissioning'  => ['nullable', 'date'],
            'date_of_retirement'     => ['nullable', 'date'],
            'reason_of_retirement'   => ['nullable', 'string', 'max:255'],
            'corps_regiment'         => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'ex_army_unit'           => ['nullable', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaTextRegex()],
            'trade'                  => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'pma_lc_ots'             => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],

            // Section E — Contact & Bank
            'residence_phone'        => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'emergency_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'cell_no'                => ['required', 'string', 'regex:' . $this->contactRegex()],
            'contact_email'          => ['required', 'email:rfc,dns', 'max:255'],
            'present_address'        => ['required', 'string', 'min:10', 'max:1000'],
            'permanent_address'      => ['required', 'string', 'min:10', 'max:1000'],
            'banks'                  => ['required', 'array', 'min:2'],
            'banks.*.account_category' => ['required', 'string', Rule::in(['personal', 'company_operated'])],
            'banks.*.account_title'  => ['required', 'string', 'min:3', 'max:255', 'regex:' . $this->nameRegex()],
            'banks.*.account_no'     => ['required', 'string', 'min:8', 'max:24', 'regex:/^[0-9]+$/'],
            'banks.*.bank_name'      => ['required', 'string', 'min:2', 'max:255', 'regex:' . $this->bankInstitutionNameRegex()],
            'banks.*.branch_code'    => ['required', 'string', 'min:1', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
            'banks.*.branch_address' => ['required', 'string', 'min:2', 'max:500', 'regex:' . $this->alphaNumericTextRegex()],
            'banks.*.iban'           => ['nullable', 'string', 'max:34', 'regex:/^[A-Z0-9]+$/'],
            'banks.*.account_type'   => ['required', Rule::in(['Saving', 'Current'])],
            'banks.*.is_salary_account' => ['required', 'boolean'],

            // Family
            'family'                         => ['nullable', 'array'],
            'family.*.name'                  => ['required_with:family.*', 'string', 'min:3', 'max:50', 'regex:' . $this->localePersonNameRegex()],
            'family.*.gender'                => ['required_with:family.*', Rule::in(['Male', 'Female'])],
            'family.*.dob'                   => ['required_with:family.*', 'date', 'before:today'],
            'family.*.relation'              => ['required_with:family.*', 'string', 'max:100'],
            'family.*.occupation'            => ['nullable', 'string', 'max:255'],

            // Academics
            'academics'                      => ['nullable', 'array'],
            'academics.*.degree'             => ['required_with:academics.*', 'string', $this->maxWordsRule(10, 'Certificate / degree')],
            'academics.*.grade_cgpa'         => ['required_with:academics.*', 'string', $this->maxWordsRule(5, 'Grade / CGPA')],
            'academics.*.start_date'         => ['required_with:academics.*', 'date'],
            'academics.*.end_date'           => ['required_with:academics.*', 'date'],
            'academics.*.field_of_study'     => ['nullable', 'string', 'max:80'],
            'academics.*.institute'          => ['nullable', 'string', $this->maxWordsRule(10, 'University / board / institute')],

            // Employment History
            'employments'                    => ['nullable', 'array'],
            'employments.*.organization'     => ['required_with:employments.*', 'string', 'max:255'],
            'employments.*.designation'      => ['required_with:employments.*', 'string', 'max:255'],
            'employments.*.from_date'        => ['required_with:employments.*', 'date'],
            'employments.*.to_date'          => ['required_with:employments.*', 'date'],
            'employments.*.salary'           => ['nullable', 'string', 'max:100'],
            'employments.*.reason_for_leaving' => ['nullable', 'string', 'max:500'],

            // Health
            'last_fitness_test'      => ['nullable', 'string', 'max:1000'],
            'has_disability'         => ['nullable', Rule::in(['yes', 'no'])],
            'blood_group'            => ['nullable', 'string', 'max:10'],
            'disability_type'        => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaTextRegex()],
            'disability_description' => ['nullable', 'string', 'max:1000'],

            // References
            'ref1_name'         => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'ref1_designation'  => ['nullable', 'string', 'max:255'],
            'ref1_organization' => ['nullable', 'string', 'max:255'],
            'ref1_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'ref1_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],
            'ref2_name'         => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
            'ref2_designation'  => ['nullable', 'string', 'max:255'],
            'ref2_organization' => ['nullable', 'string', 'max:255'],
            'ref2_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
            'ref2_relationship' => ['nullable', Rule::in(['Family', 'Friend', 'Academic', 'Professional', 'Other'])],

            // Files & Account
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
            // General
            'full_name.required'     => 'Full name is required.',
            'full_name.string'       => 'Name must be a valid text value.',
            'full_name.min'          => 'Name must be at least 3 characters.',
            'full_name.max'          => 'Name must not exceed 50 characters.',
            'full_name.regex'        => 'Name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            'father_name.string'     => 'Father name must be a valid text value.',
            'father_name.min'        => 'Father name must be at least 3 characters.',
            'father_name.max'        => 'Father name must not exceed 50 characters.',
            'father_name.regex'      => 'Father name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            'email.email'            => 'Please enter a valid email address.',
            'email.unique'           => 'This email is already registered.',

            'phone.regex'            => 'Phone number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',

            'cnic.required'          => 'CNIC is required.',
            'cnic.string'           => 'CNIC must be a valid string.',
            'cnic.regex'            => 'CNIC must be numerical like 3443XXXXXXX with sample 13 digit CNIC.',
            'cnic.min'              => 'CNIC must be at least 13 digits.',
            'cnic.max'              => 'CNIC must not exceed 15 digits.',
            'cnic.unique'           => 'This CNIC is already registered.',

            'cnic_expiry.required'   => 'CNIC expiry date is required.',
            'cnic_expiry.date'       => 'CNIC expiry date must be a valid date.',
            'cnic_expiry.after'      => 'CNIC expiry date must be a future date.',

            'father_cnic.regex'      => 'Father CNIC must be numerical like 3443XXXXXXX with sample 13 digit CNIC.',
            'father_cnic.min'        => 'Father CNIC must be at least 13 digits.',
            'father_cnic.max'        => 'Father CNIC must not exceed 15 digits.',

            'ntn.regex'              => 'NTN must be either 7 digits or 13 digits.',

            'nationality.required'   => 'Nationality is required.',
            'nationality.regex'      => 'Nationality must be valid text (letters from any language, spaces, and standard punctuation).',

            'dob.required'           => 'Date of Birth is required.',
            'dob.before'             => 'Date of Birth must be before today.',

            'domicile_district.regex' => 'Domicile district may only contain letters, numbers, and standard punctuation.',
            'domicile_province.regex' => 'Domicile province may only contain letters and standard punctuation.',
            'city_of_birth.regex'     => 'City of birth may only contain letters, numbers, and standard punctuation.',
            'religion.regex'          => 'Religion may only contain letters and standard punctuation.',
            'sect.regex'              => 'Sect may only contain letters and standard punctuation.',

            'marital_status.required' => 'Marital status is required.',
            'spouse_name.required_if' => 'Spouse name is required when marital status is Married.',

            'spouse_name.regex'       => 'Spouse name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'nok_name.regex'          => 'Next of kin name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',

            'nok_cnic.regex'          => 'Next of kin CNIC must be numerical like 3443XXXXXXX with sample 13 digit CNIC.',
            'nok_cnic.min'            => 'Next of kin CNIC must be at least 13 digits.',
            'nok_cnic.max'            => 'Next of kin CNIC must not exceed 15 digits.',

            'nok_relation.regex'      => 'Next of kin relation may only contain letters and standard punctuation.',
            'nok_dob.before'          => 'Next of kin date of birth must be before today.',
            'nok_contact.regex'       => 'NOK contact must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',

            // Employment
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists'   => 'Selected organization does not exist.',
            'sbu_id.required'          => 'SBU is required.',
            'sbu_id.exists'            => 'Selected SBU does not exist.',
            'department_ids.required'  => 'Department is required for this role level. Please select at least one department.',
            'department_ids.min'       => 'Please select at least one department.',
            'department_id.required'   => 'Department is required.',
            'department_id.exists'     => 'Selected department does not exist.',
            'role_id.required'         => 'Role is required.',
            'role_id.exists'           => 'Selected role does not exist.',

            'employee_type.regex'      => 'Employee type may only contain letters and standard punctuation.',
            'employment_type.required' => 'Select Permanent or Contractual.',
            'employment_type.in'       => 'The selected permanent or contractual option is invalid.',
            'designation.regex'        => 'Designation may only contain letters, spaces, and punctuation (like dot or hyphen).',
            'grade.max'               => 'The grade field must not exceed 10 characters.',
            'grade.regex'             => 'Grade may only contain letters, numbers, spaces, and hyphens.',
            'branch.regex'            => 'Branch may only contain letters, spaces, and standard punctuation.',
            'vendor.regex'            => 'Vendor may only contain letters, spaces, and standard punctuation.',

            'join_date.required'       => 'Date of joining is required.',
            'join_date.date'           => 'Date of joining must be a valid date.',

            'employment_category.required' => 'Resource type is required.',
            'employment_category.in'       => 'The selected resource type is invalid.',
            'contractual_type.required'    => 'Contract type is required when Contractual is selected.',
            'contract_start_date.required' => 'Contract start date is required for a time-bound contract.',
            'contract_end_date.required'  => 'Contract end date is required for a time-bound contract.',
            'contract_end_date.after_or_equal' => 'Contract end date must be on or after the start date.',
            'verification_status.required' => 'Verification Status is required.',
            'msr_letter_no.required'       => 'MSR letter number and date is required when status is Cleared or Not Cleared.',
            'addressee.required'           => 'Addressee is required when status is Cleared or Not Cleared.',
            'addressee.min'                => 'Addressee must be at least 2 characters.',
            'verifying_authority.required' => 'Verifying authority is required when status is Cleared or Not Cleared.',
            'verifying_authority.min'      => 'Verifying authority must be at least 2 characters.',
            'verification_letter_no.required' => 'Verification letter number and date is required when status is Cleared or Not Cleared.',
            'next_verification_date.required' => 'Next verification date is required when status is Cleared or Not Cleared.',
            'next_verification_date.after_or_equal' => 'Next verification date must be today or a future date.',
            'police_remarks.required'      => 'Remarks are required when status is Cleared or Not Cleared.',
            'police_remarks.min'           => 'Remarks must be at least 2 characters when status is Cleared or Not Cleared.',

            // Ex-Forces
            'msr_letter_no.regex'          => 'MSR letter number may only contain letters, numbers, slashes, hyphens, and underscores.',
            'addressee.regex'              => 'Addressee may only contain letters and standard punctuation.',
            'verifying_authority.regex'    => 'Verifying authority may only contain letters and standard punctuation.',
            'verification_letter_no.regex' => 'Verification letter number may only contain letters, numbers, slashes, hyphens, and underscores.',
            'service_no.regex'             => 'Service number may only contain letters, numbers, slashes, hyphens, and underscores.',
            'rank.regex'                   => 'Rank may only contain letters, numbers, spaces, dots, hyphens, and slashes.',
            'medical_category.regex'       => 'Medical category may only contain letters, numbers, spaces, and standard punctuation.',
            'corps_regiment.regex'         => 'Corps / Regiment must contain valid text only.',
            'ex_army_unit.regex'           => 'Ex army unit must contain valid text only.',
            'trade.regex'                  => 'Trade must contain valid text only.',
            'pma_lc_ots.regex'             => 'PMA/LC/OTS may only contain letters, numbers, spaces, and standard punctuation.',

            // Contact & Bank
            'residence_phone.regex'    => 'Residence phone must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'emergency_contact.regex'  => 'Emergency contact must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'cell_no.required'         => 'Cell number is required.',
            'cell_no.regex'            => 'Cell number must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'contact_email.required'   => 'Email is required.',
            'contact_email.email'      => 'Please enter a valid email address.',
            'present_address.required' => 'Present address is required.',
            'present_address.min'      => 'Present address must be at least 10 characters.',
            'permanent_address.required' => 'Permanent address is required.',
            'permanent_address.min'      => 'Permanent address must be at least 10 characters.',

            'account_title.required'   => 'Account title is required.',
            'account_title.regex'      => 'Account title may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'account_no.required'      => 'Account number is required.',
            'account_no.min'           => 'Account number must be at least 8 digits.',
            'account_no.max'           => 'Account number must not exceed 24 digits.',
            'account_no.regex'         => 'Account number must contain digits only.',
            'bank_branch.required'     => 'Bank & branch is required.',
            'account_type.required'    => 'Account type is required.',

            'banks.required' => 'Save at least one bank account.',
            'banks.min' => 'Save at least two bank accounts: one Personal and one Company operated.',
            'banks.*.account_category.required' => 'Select account category for each bank account.',
            'banks.*.account_category.in' => 'Account category must be Personal or Company operated.',
            'banks.*.account_title.required' => 'Account title is required for each bank account.',
            'banks.*.bank_name.required' => 'Bank name is required for each bank account.',
            'banks.*.bank_name.regex' => 'Enter the real bank name (letters required). Numbers-only or account-style values are not accepted.',
            'banks.*.branch_code.required' => 'Branch code is required for each bank account.',
            'banks.*.branch_code.regex' => 'Branch code may only contain letters, numbers, and hyphens (no spaces). Use a short code such as GL-102 or HQ01.',
            'banks.*.branch_address.required' => 'Branch address is required for each bank account.',
            'banks.*.iban.regex' => 'IBAN must contain letters and digits only (no spaces).',
            'banks.*.iban.max' => 'IBAN must not exceed 34 characters.',
            'banks.*.account_no.required' => 'Account number is required for each bank account.',
            'banks.*.account_type.required' => 'A/C type is required for each bank account.',
            'banks.*.is_salary_account.required' => 'Each bank must indicate whether it is the salary account.',

            // Family
            'family.*.name.required_with' => 'Family member name is required.',
            'family.*.name.regex'         => 'Family member name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'family.*.gender.required_with' => 'Family member gender is required.',
            'family.*.dob.required_with'    => 'Family member date of birth is required.',
            'family.*.dob.before'           => 'Family member date of birth must be before today.',
            'family.*.relation.required_with' => 'Family member relation is required.',

            // Academics
            'academics.*.degree.required_with'     => 'Degree name is required.',
            'academics.*.grade_cgpa.required_with'  => 'Grade / CGPA is required.',
            'academics.*.start_date.required_with'  => 'Academic start date is required.',
            'academics.*.end_date.required_with'    => 'Academic end date is required.',
            'academics.*.field_of_study.max'        => 'Field of study can be at most 80 characters.',

            // Employment History
            'employments.*.organization.required_with' => 'Organization name is required.',
            'employments.*.designation.required_with'  => 'Designation is required.',
            'employments.*.from_date.required_with'    => 'From date is required.',
            'employments.*.to_date.required_with'      => 'To date is required.',

            // Health
            'disability_type.regex'    => 'Disability type must contain valid text only.',

            // References
            'ref1_name.regex'          => 'Reference 1 name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'ref1_contact.regex'       => 'Reference 1 contact must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',
            'ref2_name.regex'          => 'Reference 2 name may only contain letters, spaces, apostrophes, dots, hyphens, and underscores.',
            'ref2_contact.regex'       => 'Reference 2 contact must contain only digits and may include a leading + sign. Length must be between 10 and 15 digits.',

            // Account creation
            'password.required_if'     => 'Password is required when creating a user account.',
            'password.min'             => 'Password must be at least 8 characters.',
            'profile_photo.mimes'      => 'Profile photo must be JPG or PNG.',
            'profile_photo.max'        => 'Profile photo must be at most 5MB.',
        ];
    }
}
