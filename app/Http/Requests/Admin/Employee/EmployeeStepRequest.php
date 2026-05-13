<?php

namespace App\Http\Requests\Admin\Employee;

use App\Http\Requests\Admin\Employee\Concerns\NormalizesBankRowsFromRequest;
use App\Http\Requests\Admin\Employee\Concerns\NormalizesNokRelationFields;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesExactlyOneSalaryBank;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesEmployeeRoleScope;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesUniqueBankIdentifiers;
use App\Http\Requests\Admin\Employee\Concerns\ValidatesUniqueContactNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeStepRequest extends FormRequest
{
    use NormalizesBankRowsFromRequest;
    use NormalizesNokRelationFields;
    use ValidatesExactlyOneSalaryBank;
    use ValidatesEmployeeRoleScope;
    use ValidatesUniqueBankIdentifiers;
    use ValidatesUniqueContactNumbers;

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ((int) $this->input('step') === 5) {
                $this->assertAtLeastOneSalaryBank($v);
            }
            if ((int) $this->input('step') === 5 || (string) $this->input('subsection') === 'bank_row') {
                $this->assertUniqueBankIdentifiers($v);
            }
            if ((int) $this->input('step') === 6 || (string) $this->input('subsection') === 'contact') {
                $this->assertUniqueContactNumbers($v);
            }
            if ((int) $this->input('step') === 6) {
                $this->assertFamilyNextOfKinRules($v);
            }
            if ((string) $this->input('subsection') === 'family_row') {
                $this->assertFamilyRowNextOfKinRules($v);
            }
        });
    }

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
        $stepRaw = $this->input('step');
        if ($stepRaw !== null && $stepRaw !== '' && (int) $stepRaw === 2) {
            $rawDept = $this->input('department_ids', []);
            if (! is_array($rawDept)) {
                $rawDept = $rawDept !== null && $rawDept !== '' ? [$rawDept] : [];
            }
            $deptIds = array_values(array_unique(array_filter(array_map('intval', $rawDept))));
            $rawFloors = $this->input('assigned_floor_ids', []);
            if (! is_array($rawFloors)) {
                $rawFloors = $rawFloors !== null && $rawFloors !== '' ? [$rawFloors] : [];
            }
            $floorIds = array_values(array_unique(array_filter(array_map('intval', $rawFloors))));
            $this->merge([
                'department_ids' => $deptIds,
                'department_id'  => $deptIds[0] ?? null,
                'assigned_floor_ids' => $floorIds,
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

        if ($this->filled('intern_type')) {
            $this->merge(['intern_type' => strtolower(trim((string) $this->input('intern_type')))]);
        }

        if ($this->filled('ntn')) {
            $this->merge(['ntn' => preg_replace('/\D/', '', (string) $this->input('ntn'))]);
        }

        $trimFields = ['full_name', 'father_name', 'name', 'present_address', 'permanent_address', 'relation', 'occupation', 'nok_relation_other'];
        foreach ($trimFields as $tf) {
            if ($this->filled($tf)) {
                $this->merge([$tf => trim((string)$this->input($tf))]);
            }
        }

        $this->normalizeNokRelationFromRequest();

        if ($this->input('engagement_mode') === 'on_site') {
            $this->merge(['engagement_mode' => 'standard']);
        }

        if ($this->input('engagement_mode') === 'standard' && ! $this->filled('standard_schedule_mode')) {
            $this->merge(['standard_schedule_mode' => 'default']);
        }

        $stepRaw = $this->input('step');
        $st = ($stepRaw !== null && $stepRaw !== '') ? (int) $stepRaw : 0;
        if ($st === 1 || $st === 0) {
            $this->merge([
                'is_ex_armed_force' => $this->boolean('is_ex_armed_force') ? 1 : 0,
                'is_father_deceased' => $this->boolean('is_father_deceased') ? 1 : 0,
            ]);
        }
        if ($st === 5 || $st === 0) {
            $this->normalizeBankRowsFromRequest();
        }

        if ($st === 6 && is_array($this->input('family'))) {
            $family = $this->input('family');
            foreach ($family as $i => $fr) {
                if (! is_array($fr)) {
                    continue;
                }
                if (! empty($fr['nok_cnic'])) {
                    $family[$i]['nok_cnic'] = str_replace('-', '', (string) $fr['nok_cnic']);
                }
                if (! empty($fr['nok_contact'])) {
                    $family[$i]['nok_contact'] = preg_replace('/[^\d+]/', '', (string) $fr['nok_contact']);
                }
            }
            $this->merge(['family' => $family]);
        }

        foreach (['ref1_relationship', 'ref2_relationship'] as $refRel) {
            if ($this->has($refRel) && trim((string) $this->input($refRel)) === '') {
                $this->merge([$refRel => null]);
            }
        }

        if ($this->filled('termination_reason')) {
            $this->merge(['termination_reason' => trim((string) $this->input('termination_reason'))]);
        }

        if ($this->filled('suspension_reason')) {
            $this->merge(['suspension_reason' => trim((string) $this->input('suspension_reason'))]);
        }

        if ((string) $this->input('subsection') === 'family_row') {
            if ($this->filled('nok_cnic')) {
                $this->merge(['nok_cnic' => str_replace('-', '', (string) $this->input('nok_cnic'))]);
            }
            if ($this->filled('nok_contact')) {
                $this->merge(['nok_contact' => preg_replace('/[^\d+]/', '', (string) $this->input('nok_contact'))]);
            }
        }

        if ((string) $this->input('subsection') === 'bank_row') {
            $this->merge([
                'account_no'       => preg_replace('/\s+/', '', (string) $this->input('account_no', '')),
                'iban'             => strtoupper(preg_replace('/\s+/', '', (string) $this->input('iban', ''))),
                'account_title'    => trim((string) $this->input('account_title', '')),
                'bank_name'        => trim((string) $this->input('bank_name', '')),
                'branch_code'      => trim((string) $this->input('branch_code', '')),
                'branch_address'   => trim((string) $this->input('branch_address', '')),
                'is_salary_account' => $this->boolean('is_salary_account'),
            ]);
        }

        $stepGrace = ($this->input('step') !== null && $this->input('step') !== '') ? (int) $this->input('step') : 0;
        if ($stepGrace === 2 || $stepGrace === 0) {
            $openingGracePeriod = null;
            $closingGracePeriod = null;
            if (array_key_exists('grace_period', $this->all())) {
                $g = $this->input('grace_period');
                if ($g !== null && $g !== '') {
                    $openingGracePeriod = $g;
                    $closingGracePeriod = $g;
                }
            } else {
                $openingGracePeriod = $this->input('opening_grace_period');
                $closingGracePeriod = $this->input('closing_grace_period');
                if (($openingGracePeriod === null || $openingGracePeriod === '') && ($closingGracePeriod !== null && $closingGracePeriod !== '')) {
                    $openingGracePeriod = $closingGracePeriod;
                }
                if ($openingGracePeriod !== null && $openingGracePeriod !== '') {
                    $closingGracePeriod = $openingGracePeriod;
                }
            }
            $this->merge([
                'opening_grace_period' => $openingGracePeriod,
                'closing_grace_period' => $closingGracePeriod,
            ]);
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
        return '/^[0-9]{11,15}$/';
    }

    protected function residencePhoneRegex(): string
    {
        return '/^[0-9]{7,15}$/';
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

        // Base rules strictly limited to wizard meta-data
        $baseRules = [
            'step'        => ['required', 'integer', 'min:1', 'max:6'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];

        $stepRules = [];

        if ($step === 1) {
            $stepRules = array_merge([
                'full_name' => ['required', 'string', 'min:3', 'max:50', 'regex:' . $this->localePersonNameRegex()],
                'father_name' => ['required', 'string', 'min:3', 'max:50', 'regex:' . $this->localePersonNameRegex()],
                'email' => [
                    'nullable',
                    'email:rfc,dns',
                    'max:50',
                    Rule::unique('employees', 'email')->ignore($employeeId),
                ],
                'phone' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                'cnic' => [
                    'bail',
                    'required',
                    'string',
                    'regex:' . $this->cnicRegex(),
                    'min:13',
                    'max:15',
                    Rule::unique('employees', 'cnic')->ignore($employeeId),
                ],
                'cnic_issue_date' => ['required', 'date', 'before_or_equal:today', 'after:dob'],
                'cnic_expiry' => ['required', 'date', 'after:today'],
                'father_cnic' => [
                    'bail',
                    'required_if:is_father_deceased,0',
                    'nullable',
                    'string',
                    'regex:' . $this->cnicRegex(),
                    'min:13',
                    'max:15'
                ],
                'ntn' => ['nullable', 'string', 'regex:/^(?:[0-9]{7}|[0-9]{13})$/'],
                'is_ex_armed_force' => ['nullable', 'boolean'],
                'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
                'nationality' => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->localeNameTextRegex()],
                'dob' => ['required', 'date', 'before:today'],
                'domicile_district' => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphanumericLabelRegex()],
                'domicile_province' => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->localeNameTextRegex()],
                'city_of_birth' => ['nullable', 'string', 'max:50', 'regex:' . $this->localeAlphanumericLabelRegex()],
                'religion' => ['required', 'string', 'min:2', 'max:50', 'regex:' . $this->alphaTextRegex()],
                'sect' => ['required', 'string', 'min:2', 'max:50', 'regex:' . $this->localeAlphaLabelRegex()],
                'marital_status' => ['required', Rule::in(['Single', 'Married', 'Separated', 'Divorced', 'Widow'])],
                'spouse_name' => [
                    'required_if:marital_status,Married', 'nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->localePersonNameRegex()
                ],
                'spouse_cnic' => [
                    'required_if:marital_status,Married', 'nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'
                ],
                'spouse_nationality' => [
                    'required_if:marital_status,Married', 'nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeNameTextRegex()
                ],
            ]);
        }
        elseif ($step === 2) {
            $stepRules = [
                'employment_category' => ['required', Rule::in(['intern', 'consultant', 'employee', 'contractual', 'engagement'])],
                'organization_id' => ['required', 'integer', 'exists:organizations,id'],
                'role_id' => ['required', 'integer', 'exists:roles,id'],
                'sbu_id' => [
                    'nullable',
                    'integer',
                    'exists:sbus,id',
                    Rule::requiredIf(fn () => ! $this->orgLevelRoleSelected()),
                ],
                'department_id' => ['nullable', 'integer', 'exists:departments,id'],
                'department_ids'   => [
                    Rule::requiredIf(fn () => $this->deptRequiredForRole()),
                    'nullable',
                    'array',
                    Rule::when($this->deptRequiredForRole(), ['min:1']),
                ],
                'department_ids.*' => ['integer', 'exists:departments,id'],
                'employee_type' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaTextRegex()],
                'designation' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaTextRegex()],
                'grade' => ['nullable', 'string', 'max:10', 'regex:' . $this->alphaNumericTextRegex()],
                'branch' => ['nullable', 'string', 'max:50', 'regex:' . $this->alphaNumericTextRegex()],
                'location' => ['nullable', 'string', 'max:255', 'regex:' . $this->bankInstitutionNameRegex()],
                'site' => ['nullable', 'string', 'max:255'],
                'join_date' => ['required', 'date', 'before_or_equal:today'],
                'floor_access' => ['nullable', 'boolean'],
                'assigned_floor_ids' => ['nullable', 'array'],
                'assigned_floor_ids.*' => [
                    'integer',
                    Rule::exists('sbu_floors', 'id')->where(function ($q) {
                        $sbuId = $this->input('sbu_id');
                        if ($sbuId) {
                            $q->where('sbu_id', (int) $sbuId);
                        }
                    }),
                ],
                'biometric_id' => ['nullable', 'string', 'max:20'],
                'employee_status' => ['required', Rule::in(['Active', 'Suspend', 'Terminated'])],
                'termination_reason' => [
                    'nullable',
                    'string',
                    'max:500',
                    Rule::requiredIf(fn () => ($this->input('employee_status') ?? '') === 'Terminated'),
                    Rule::when(
                        fn () => ($this->input('employee_status') ?? '') === 'Terminated',
                        ['min:5', 'regex:/^[^<>]+$/u']
                    ),
                ],
                'termination_date' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                    Rule::requiredIf(fn () => ($this->input('employee_status') ?? '') === 'Terminated'),
                ],
                'suspension_reason' => [
                    'nullable',
                    'string',
                    'max:500',
                    Rule::requiredIf(fn () => ($this->input('employee_status') ?? '') === 'Suspend'),
                ],
                'suspension_start_date' => [
                    'nullable',
                    'date',
                    Rule::requiredIf(fn () => ($this->input('employee_status') ?? '') === 'Suspend'),
                ],
                'suspension_end_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:suspension_start_date',
                    Rule::requiredIf(fn () => ($this->input('employee_status') ?? '') === 'Suspend'),
                ],
                'intern_type' => [
                    'nullable',
                    Rule::in(['paid', 'unpaid']),
                    'required_if:employment_category,intern',
                ],
                'intern_duration' => [
                    'nullable',
                    'string',
                    'max:10',
                    'required_if:employment_category,intern',
                ],
                'employment_type' => [
                    'nullable',
                    Rule::in(['permanent', 'contractual']),
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'),
                ],
                'contractual_type' => [
                    'nullable',
                    Rule::in(['time_bound', 'open', 'open_ended', 'project_based']),
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'employee' && $this->input('employment_type') === 'contractual'),
                ],
                'contract_start_date' => [
                    'nullable',
                    'date',
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'contractual'),
                ],
                'contract_end_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:contract_start_date',
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'contractual'),
                ],
                'probation_start_date' => ['nullable', 'date', Rule::requiredIf(fn () => $this->input('employment_category') === 'employee')],
                'probation_end_date' => ['nullable', 'date', 'after_or_equal:probation_start_date', Rule::requiredIf(fn () => $this->input('employment_category') === 'employee')],
                'probation_contract_start_date' => ['nullable', 'date'],
                'employee_contract_start_date' => [
                    'nullable',
                    'date',
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'
                        && $this->input('employment_type') === 'contractual'
                        && $this->input('contractual_type') === 'time_bound'),
                ],
                'employee_contract_end_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:employee_contract_start_date',
                    Rule::requiredIf(fn () => $this->input('employment_category') === 'employee'
                        && $this->input('employment_type') === 'contractual'
                        && $this->input('contractual_type') === 'time_bound'),
                ],
                'engagement_mode' => ['required', Rule::in(['standard', 'remote', 'shifts', 'hybrid'])],
                'hybrid_days' => [
                    'nullable',
                    'array',
                    Rule::requiredIf(fn () => $this->input('engagement_mode') === 'hybrid'),
                ],
                'standard_schedule_mode' => [
                    'nullable',
                    Rule::in(['default', 'custom']),
                    Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard'),
                ],
                'working_days' => [
                    'nullable',
                    'array',
                    Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard' && $this->input('standard_schedule_mode') === 'custom'),
                ],
                'working_start_time' => [
                    'nullable',
                    'date_format:H:i',
                    Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard' && $this->input('standard_schedule_mode') === 'custom'),
                ],
                'working_end_time' => [
                    'nullable',
                    'date_format:H:i',
                    'after:working_start_time',
                    Rule::requiredIf(fn () => $this->input('engagement_mode') === 'standard' && $this->input('standard_schedule_mode') === 'custom'),
                ],
                'grace_period' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:600'],
                'opening_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
                'closing_grace_period' => ['nullable', 'integer', 'min:0', 'max:600'],
                'sync_with_biometric' => ['nullable', 'boolean'],
            ];
        }
        elseif ($step === 3) {
            $inProcess = 'In Process';
            $stepRules = [
                'verification_status' => ['required', Rule::in(['Cleared', 'Not Cleared', $inProcess])],
                'msr_letter_no' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[0-9]+$/',
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'msr_date' => [
                    'nullable',
                    'date',
                    Rule::when(
                        fn () => ($this->input('verification_status') ?? '') !== $inProcess,
                        ['before_or_equal:today']
                    ),
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'addressee' => [
                    'nullable',
                    'string',
                    'max:100',
                    'regex:' . $this->alphaNumericTextRegex(),
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'verifying_authority' => [
                    'nullable',
                    'string',
                    'max:50',
                    'regex:' . $this->alphaTextRegex(),
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'verification_letter_no' => [
                    'nullable',
                    'string',
                    'max:100',
                    'regex:/^[A-Za-z0-9\/\-_]+$/',
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'verification_letter_date' => [
                    'nullable',
                    'date',
                    'after_or_equal:msr_date',
                    Rule::when(
                        fn () => ($this->input('verification_status') ?? '') !== $inProcess,
                        ['before_or_equal:today']
                    ),
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'next_verification_date' => [
                    'nullable',
                    'date',
                    'after:verification_letter_date',
                    'after_or_equal:today',
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
                'police_remarks' => [
                    'nullable', 
                    'string', 
                    'max:2000',
                    Rule::requiredIf(fn () => ($this->input('verification_status') ?? '') !== $inProcess),
                ],
            ];
        }
        elseif ($step === 4) {
            $stepRules = [
                'service_no' => ['nullable', 'string', 'max:50', 'regex:' . $this->alphanumericCodeRegex()],
                'rank' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\.\-\/]+$/'],
                'medical_category' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaNumericTextRegex()],
                'date_of_commissioning' => ['nullable', 'date', 'before_or_equal:today'],
                'date_of_retirement' => ['nullable', 'date', 'after_or_equal:date_of_commissioning'],
                'reason_of_retirement' => ['nullable', 'string', 'max:255'],
                'corps_regiment' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaTextRegex()],
                'ex_army_unit' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaTextRegex()],
                'trade' => ['nullable', 'string', 'max:100', 'regex:' . $this->alphaTextRegex()],
                'pma_lc_ots' => ['nullable', 'string', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
            ];
        }
        elseif ($step === 5) {
            $stepRules = [
                'banks'                        => ['required', 'array', 'min:1'],
                'banks.*.bank_detail_id'       => ['nullable', 'integer'],
                'banks.*.account_category'     => ['required', 'string', Rule::in(['Personal', 'Company'])],
                'banks.*.account_title'        => ['required', 'string', 'min:3', 'max:255', 'regex:/^[A-Za-z0-9]+(?:[A-Za-z0-9\s\.\-\'_]*[A-Za-z0-9])?$/'],
                'banks.*.account_no'           => ['required', 'string', 'min:8', 'max:24', 'regex:/^[0-9]+$/'],
                'banks.*.bank_name'            => ['required', 'string', 'min:2', 'max:255', 'regex:' . $this->bankInstitutionNameRegex()],
                'banks.*.branch_name'          => ['required', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
                'banks.*.branch_code'          => ['required', 'string', 'min:1', 'max:10', 'regex:/^[A-Za-z0-9\-]+$/'],
                'banks.*.branch_address'       => ['required', 'string', 'min:2', 'max:500', 'regex:' . $this->alphaNumericTextRegex()],
                'banks.*.iban'                 => ['required', 'string', 'min:15', 'max:34', 'regex:/^[A-Z0-9]+$/'],
                'banks.*.account_type'         => ['required', Rule::in(['Saving', 'Current'])],
                'banks.*.is_salary_account'    => ['required', 'boolean'],
            ];
        }
        elseif ($step === 6) {
            $stepRules = [
                // Contact
                'residence_phone'   => ['nullable', 'string', 'regex:' . $this->residencePhoneRegex()],
                'emergency_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                'cell_no'           => ['required', 'string', 'regex:' . $this->contactRegex()],
                'contact_email'     => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                ],
                'present_address'   => ['required', 'string', 'min:10', 'max:1000'],
                'permanent_address' => ['required', 'string', 'min:10', 'max:1000'],

                // Family
                'family'            => ['nullable', 'array'],
                'family.*.name'     => ['required_with:family.*', 'string', 'min:3', 'max:100', 'regex:' . $this->localePersonNameRegex()],
                'family.*.gender'   => ['required_with:family.*', Rule::in(['Male', 'Female'])],
                'family.*.dob'      => ['required_with:family.*', 'date', 'before:today'],
                'family.*.relation' => ['required_with:family.*', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
                'family.*.relation_other' => ['nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
                'family.*.occupation' => ['nullable', 'string', 'max:100', 'regex:' . $this->localeAlphanumericLabelRegex()],
                'family.*.is_next_of_kin' => ['nullable', 'boolean'],
                'family.*.nok_cnic' => ['nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
                'family.*.nok_cnic_expiry_date' => ['nullable', 'date', 'after:today'],
                'family.*.nok_contact' => ['nullable', 'string', 'max:15', 'regex:' . $this->contactRegex()],

                // Academics
                'academics'                => ['nullable', 'array'],
                'academics.*.degree'       => ['required_with:academics.*', 'string', 'max:50'],
                'academics.*.grade_cgpa'   => ['required_with:academics.*', 'string', 'max:20'],
                'academics.*.start_date'   => ['required_with:academics.*', 'date', 'before_or_equal:today'],
                'academics.*.end_date'     => ['required_with:academics.*', 'date', 'after_or_equal:academics.*.start_date'],
                'academics.*.institute'    => ['required_with:academics.*', 'string', 'max:255'],

                // Certificates
                'certificates'                      => ['nullable', 'array'],
                'certificates.*.certificate_name'   => ['required_with:certificates.*', 'string', 'max:150'],
                'certificates.*.start_date'         => ['required_with:certificates.*', 'date', 'before_or_equal:today'],
                'certificates.*.end_date'           => ['required_with:certificates.*', 'date', 'after_or_equal:certificates.*.start_date'],
                'certificates.*.institute'          => ['required_with:certificates.*', 'string', 'max:255'],

                // Employment History
                'employments'              => ['nullable', 'array'],
                'employments.*.organization' => ['required_with:employments.*', 'string', 'min:2', 'max:255'],
                'employments.*.designation'  => ['required_with:employments.*', 'string', 'min:2', 'max:255'],
                'employments.*.from_date'    => ['required_with:employments.*', 'date', 'before_or_equal:today'],
                'employments.*.to_date'      => ['required_with:employments.*', 'date', 'after_or_equal:employments.*.from_date'],

                // Medical
                'last_fitness_test'          => ['nullable', 'string', 'max:1000'],
                'last_fitness_test_date'     => ['nullable', 'date', 'before_or_equal:today'],
                'last_fitness_test_result'   => ['nullable', Rule::in(['Positive', 'Negative'])],
                'has_disability'             => ['nullable', Rule::in(['yes', 'no'])],
                'blood_group'                => ['nullable', 'string', 'regex:/^(A|B|AB|O)[+-]$/'],
                'disability_type'            => ['nullable', 'string', 'max:100'],
                'disability_description'     => ['nullable', 'string', 'max:1000'],
                'has_chronic_disease'        => ['nullable', Rule::in(['yes', 'no'])],
                'chronic_disease_description' => ['nullable', 'string', 'max:1000'],

                // References
                'ref1_name'         => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
                'ref1_designation'  => ['nullable', 'string', 'max:255'],
                'ref1_organization' => ['nullable', 'string', 'max:255'],
                'ref1_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                'ref1_relationship' => ['nullable', Rule::in($this->referenceRelationshipValues())],

                'ref2_name'         => ['nullable', 'string', 'min:3', 'max:100', 'regex:' . $this->nameRegex()],
                'ref2_designation'  => ['nullable', 'string', 'max:255'],
                'ref2_organization' => ['nullable', 'string', 'max:255'],
                'ref2_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                'ref2_relationship' => ['nullable', Rule::in($this->referenceRelationshipValues())],

                // Account
                'create_user_account' => ['nullable', 'boolean'],
                'password' => [
                    'nullable', 'string', 'min:8', 'max:64',
                    'required_if:create_user_account,true',
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
                    'profile_photo'          => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,svg'],
                ]);

            case 'attachment':
                return array_merge($rules, [
                    'attachments.*.name' => ['required', 'string', 'max:255'],
                    'attachments.*.type' => ['nullable', 'string', 'max:100'],
                    'attachments.*.description' => ['nullable', 'string', 'max:1000'],
                    'attachments.*.files' => ['required', 'array', 'min:1'],
                    'attachments.*.files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,zip,xls,txt'],
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
                    'residence_phone'   => ['nullable', 'string', 'regex:' . $this->residencePhoneRegex()],
                    'emergency_contact' => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                ]);

            case 'family_row':
                return array_merge($rules, [
                    'family_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('employee_family_members', 'id')->where('employee_id', (int) $this->input('employee_id')),
                    ],
                    'name'           => ['required', 'string', 'min:3', 'max:100', 'regex:' . $this->localePersonNameRegex()],
                    'gender'         => ['required', Rule::in(['Male', 'Female'])],
                    'dob'            => ['required', 'date', 'before:today'],
                    'relation'       => ['required', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
                    'relation_other' => ['required_if:relation,Other', 'nullable', 'string', 'min:2', 'max:100', 'regex:' . $this->localeAlphaLabelRegex()],
                    'occupation'     => ['nullable', 'string', 'max:100', 'regex:' . $this->localeAlphanumericLabelRegex()],
                    'is_next_of_kin' => ['nullable', 'boolean'],
                    'nok_cnic'       => ['nullable', 'string', 'regex:' . $this->cnicRegex(), 'min:13', 'max:15'],
                    'nok_cnic_expiry_date' => ['nullable', 'date', 'after:today'],
                    'nok_contact'    => ['nullable', 'string', 'max:15', 'regex:' . $this->contactRegex()],
                ]);

            case 'academic_row':
                return array_merge($rules, [
                    'degree'         => ['required', 'string', 'max:50', $this->maxWordsRule(20, 'Degree type')],
                    'degree_title'   => ['required', 'string', 'max:100', $this->maxWordsRule(20, 'Degree title')],
                    'grade_cgpa'     => ['required', 'string', 'max:20', $this->maxWordsRule(10, 'Grade / CGPA')],
                    'start_date'     => ['required', 'date', 'before_or_equal:today'],
                    'end_date'       => ['required', 'date', 'after_or_equal:start_date'],
                    'field_of_study' => ['nullable', 'string', 'max:50', 'regex:' . $this->alphaNumericTextRegex()],
                    'institute'      => ['nullable', 'string', 'max:150', $this->maxWordsRule(20, 'University')],
                    'transcript_file'=> ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                    'degree_file'    => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                ]);

            case 'employment_row':
                return array_merge($rules, [
                    'organization'       => ['required', 'string', 'max:150'],
                    'designation'        => ['required', 'string', 'max:100'],
                    'from_date'          => ['required', 'date', 'before_or_equal:today'],
                    'to_date'            => ['required', 'date', 'after_or_equal:from_date'],
                    'salary'             => ['nullable', 'string', 'max:15'],
                    'reason_for_leaving' => ['nullable', 'string', 'max:200'],
                    'hr_contact'         => ['nullable', 'string', 'max:15'],
                    'hr_email'           => ['nullable', 'email', 'max:100'],
                    'experience_letter'  => ['required_without:employment_id', 'nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                    'salary_slip'        => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                ]);

            case 'certificate_row':
                return array_merge($rules, [
                    'certificate_id'   => [
                        'nullable',
                        'integer',
                        Rule::exists('employee_certificates', 'id')->where('employee_id', (int) $this->input('employee_id')),
                    ],
                    'certificate_name' => ['required', 'string', 'max:150', $this->maxWordsRule(20, 'Certificate name')],
                    'start_date'       => ['required', 'date', 'before_or_equal:today'],
                    'end_date'         => ['required', 'date', 'after_or_equal:start_date'],
                    'institute'        => ['required', 'string', 'max:255', $this->maxWordsRule(20, 'Institute')],
                    'certificate_file' => ['required_without:certificate_id', 'nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                ]);

            case 'medical':
                return array_merge($rules, [
                    'last_fitness_test'        => ['nullable', 'string', 'max:500'],
                    'last_fitness_test_date'   => ['nullable', 'date', 'before_or_equal:today'],
                    'last_fitness_test_result' => ['nullable', Rule::in(['Positive', 'Negative'])],
                    'has_disability'           => ['required', Rule::in(['yes', 'no'])],
                    'blood_group'              => ['nullable', 'string', 'max:10', 'regex:' . $this->bloodGroupRegex()],
                    'disability_type'          => [
                        'required_if:has_disability,yes',
                        'nullable',
                        'string',
                        'max:100',
                        Rule::in(['Physical', 'Visual', 'Hearing', 'Speech', 'Other']),
                    ],
                    'disability_description'   => [
                        'nullable',
                        'string',
                        'max:1000',
                        Rule::requiredIf(function () {
                            return (string) $this->input('disability_type') === 'Other';
                        }),
                    ],
                    'has_chronic_disease'      => ['required', Rule::in(['yes', 'no'])],
                    'chronic_disease_description' => [
                        'nullable',
                        'string',
                        'max:1000',
                        Rule::requiredIf(function () {
                            return (string) $this->input('has_chronic_disease') === 'yes';
                        }),
                    ],
                    'medical_file' => ['nullable', 'file', 'max:20480', 'mimes:jpg,jpeg,png,pdf'],
                ]);

            case 'bank_row':
                return array_merge($rules, [
                    'bank_detail_id'     => [
                        'nullable',
                        'integer',
                        Rule::exists('employee_bank_details', 'id')->where('employee_id', (int) $this->input('employee_id')),
                    ],
                    'account_category'   => ['required', 'string', Rule::in(['Personal', 'Company'])],
                    'account_title'      => ['required', 'string', 'min:3', 'max:255', 'regex:/^[A-Za-z0-9]+(?:[A-Za-z0-9\s\.\-\'_]*[A-Za-z0-9])?$/'],
                    'account_no'         => ['required', 'string', 'min:8', 'max:24', 'regex:/^[0-9]+$/'],
                    'bank_name'          => ['required', 'string', 'min:2', 'max:255', 'regex:' . $this->bankInstitutionNameRegex()],
                    'branch_name'        => ['required', 'string', 'min:2', 'max:255', 'regex:' . $this->alphaNumericTextRegex()],
                    'branch_code'        => ['required', 'string', 'min:1', 'max:10', 'regex:/^[A-Za-z0-9\-]+$/'],
                    'branch_address'       => ['required', 'string', 'min:2', 'max:500', 'regex:' . $this->alphaNumericTextRegex()],
                    'iban'               => ['required', 'string', 'min:15', 'max:34', 'regex:/^[A-Z0-9]+$/'],
                    'account_type'       => ['required', Rule::in(['Saving', 'Current'])],
                    'is_salary_account'  => ['required', 'boolean'],
                ]);

            case 'references':
                return array_merge($rules, [
                    'ref1_name'         => ['nullable', 'string', 'max:50', 'regex:' . $this->nameRegex()],
                    'ref1_designation'  => ['nullable', 'string', 'max:50'],
                    'ref1_organization' => ['nullable', 'string', 'max:100'],
                    'ref1_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                    'ref1_relationship' => ['nullable', Rule::in($this->referenceRelationshipValues())],
                    'ref2_name'         => ['nullable', 'string', 'max:50', 'regex:' . $this->nameRegex()],
                    'ref2_designation'  => ['nullable', 'string', 'max:50'],
                    'ref2_organization' => ['nullable', 'string', 'max:100'],
                    'ref2_contact'      => ['nullable', 'string', 'regex:' . $this->contactRegex()],
                    'ref2_relationship' => ['nullable', Rule::in($this->referenceRelationshipValues())],
                ]);
        }

        return $rules;
    }

    protected function familyRowIsNextOfKinTruthy(?array $row): bool
    {
        if ($row === null) {
            return false;
        }

        return filter_var($row['is_next_of_kin'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || (isset($row['is_next_of_kin']) && (string) $row['is_next_of_kin'] === '1');
    }

    protected function assertFamilyNextOfKinRules($v): void
    {
        $family = $this->input('family', []);
        if (! is_array($family)) {
            return;
        }

        $filledRowIndexes = [];
        foreach ($family as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            if ($name === '') {
                continue;
            }
            $filledRowIndexes[] = $idx;
            $relation = (string) ($row['relation'] ?? '');
            if ($relation === 'Other') {
                $other = isset($row['relation_other']) ? trim((string) $row['relation_other']) : '';
                if ($other === '') {
                    $v->errors()->add("family.$idx.relation_other", 'Specify relation when Other is selected.');
                }
            }
        }

        if (count($filledRowIndexes) === 0) {
            return;
        }

        $nokIndexes = [];
        foreach ($filledRowIndexes as $idx) {
            $row = $family[$idx];
            if (! is_array($row)) {
                continue;
            }
            if ($this->familyRowIsNextOfKinTruthy($row)) {
                $nokIndexes[] = $idx;
            }
        }

        if (count($nokIndexes) > 1) {
            $v->errors()->add('family', 'Only one family member can be Next of Kin.');

            return;
        }

        if (count($nokIndexes) === 0) {
            return;
        }

        $idx = $nokIndexes[0];
        $row = $family[$idx];
        $labels = [
            'nok_cnic'             => 'Next of Kin CNIC',
            'nok_cnic_expiry_date' => 'Next of Kin CNIC expiry',
            'nok_contact'          => 'Next of Kin contact',
        ];
        foreach ($labels as $field => $label) {
            $val = isset($row[$field]) ? trim((string) $row[$field]) : '';
            if ($val === '') {
                $v->errors()->add("family.$idx.$field", $label . ' is required for the member marked as Next of Kin.');

                continue;
            }
            if ($field === 'nok_cnic' && ! preg_match($this->cnicRegex(), $val)) {
                $v->errors()->add("family.$idx.$field", 'Next of Kin CNIC must be 13–15 digits.');
            }
            if ($field === 'nok_contact' && ! preg_match($this->contactRegex(), $val)) {
                $v->errors()->add("family.$idx.$field", 'Next of Kin contact must be a valid phone number (11–15 digits).');
            }
            if ($field === 'nok_cnic_expiry_date') {
                try {
                    $d = \Carbon\Carbon::parse($val)->startOfDay();
                    if ($d->lte(\Carbon\Carbon::today())) {
                        $v->errors()->add("family.$idx.$field", 'Next of Kin CNIC expiry must be after today.');
                    }
                } catch (\Throwable $e) {
                    $v->errors()->add("family.$idx.$field", 'Next of Kin CNIC expiry must be a valid date.');
                }
            }
        }
    }

    protected function assertFamilyRowNextOfKinRules($v): void
    {
        $row = $this->all();
        if (! $this->familyRowIsNextOfKinTruthy($row)) {
            return;
        }

        if (((string) ($row['relation'] ?? '')) === 'Other') {
            $other = isset($row['relation_other']) ? trim((string) $row['relation_other']) : '';
            if ($other === '') {
                $v->errors()->add('relation_other', 'Specify relation when Other is selected.');
            }
        }

        $labels = [
            'nok_cnic'             => 'Next of Kin CNIC',
            'nok_cnic_expiry_date' => 'Next of Kin CNIC expiry',
            'nok_contact'          => 'Next of Kin contact',
        ];
        foreach ($labels as $field => $label) {
            $val = isset($row[$field]) ? trim((string) $row[$field]) : '';
            if ($val === '') {
                $v->errors()->add($field, $label . ' is required when marking this member as Next of Kin.');

                continue;
            }
            if ($field === 'nok_cnic' && ! preg_match($this->cnicRegex(), $val)) {
                $v->errors()->add($field, 'Next of Kin CNIC must be 13–15 digits.');
            }
            if ($field === 'nok_contact' && ! preg_match($this->contactRegex(), $val)) {
                $v->errors()->add($field, 'Next of Kin contact must be a valid phone number (11–15 digits).');
            }
            if ($field === 'nok_cnic_expiry_date') {
                try {
                    $d = \Carbon\Carbon::parse($val)->startOfDay();
                    if ($d->lte(\Carbon\Carbon::today())) {
                        $v->errors()->add($field, 'Next of Kin CNIC expiry must be after today.');
                    }
                } catch (\Throwable $e) {
                    $v->errors()->add($field, 'Next of Kin CNIC expiry must be a valid date.');
                }
            }
        }
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
            'full_name.regex' => 'Name must contain letters (any language), spaces, apostrophes, dots, hyphens, or underscores — not numbers-only.',

            'father_name.required' => 'Father name is required.',
            'father_name.string' => "Father name must be a valid text value.",
            'father_name.min' => "Father name must be at least 3 characters.",
            'father_name.regex' => 'Father name must contain letters (any language), spaces, apostrophes, dots, hyphens, or underscores — not numbers-only.',

            'father_cnic.required' => 'Father CNIC is required.',
            'father_cnic.regex' => 'Father CNIC must be in the standard format (13–15 digits).',

            'gender.required' => 'Gender is required.',

            'domicile_province.required' => 'Province is required.',
            'domicile_district.required' => 'District is required.',

            'religion.required' => 'Religion is required.',
            'sect.required' => 'Sect is required.',
            'cnic.required' => 'CNIC is required.',
            'cnic.regex' => 'CNIC must be in the standard format (XXXXX-XXXXXXX-X).',
            'cnic.unique' => 'This CNIC is already registered.',
            'spouse_cnic.required_if' => 'Spouse CNIC is required when status is Married.',
            'spouse_name.required_if' => 'Spouse name is required when status is Married.',
            'spouse_cnic.regex' => 'Spouse CNIC must be in the standard format (XXXXX-XXXXXXX-X).',
            'ntn.regex' => 'NTN must be either 7 digits or 13 digits.',

            // NOK
            'nok_name.required' => 'The Next of Kin (NOK) name is mandatory.',
            'nok_name.regex' => 'The Next of Kin (NOK) name must contain letters (any language) and allowed punctuation — not numbers-only.',
            'nok_cnic.required' => 'The Next of Kin (NOK) CNIC is mandatory.',
            'nok_cnic.regex' => 'The Next of Kin (NOK) CNIC must be in a valid format (XXXXX-XXXXXXX-X).',
            'nok_cnic_expiry_date.required' => 'The Next of Kin (NOK) CNIC expiry date is mandatory.',
            'nok_cnic_expiry_date.after' => 'The Next of Kin (NOK) CNIC must not be expired.',
            'nok_relation.required' => 'The relationship with the Next of Kin (NOK) is mandatory.',
            'nok_relation.regex' => 'The Next of Kin (NOK) relation must contain text only.',
            'nok_relation_type.required' => 'Select relation with NOK.',
            'nok_relation_type.in' => 'The selected relation with NOK is invalid.',
            'nok_relation_other.required_if' => 'Please specify the relation with NOK when you choose Other.',
            'nok_relation_other.regex' => 'The custom relation may only contain letters and standard punctuation.',
            'nok_dob.required' => 'The Next of Kin (NOK) date of birth is mandatory.',
            'nok_dob.before' => 'The Next of Kin (NOK) date of birth must be a past date.',
            'nok_contact.required' => 'The Next of Kin (NOK) contact number is mandatory.',
            'nok_contact.max' => 'The Next of Kin (NOK) contact number must not exceed 15 digits.',
            'nok_contact.regex' => 'The Next of Kin (NOK) contact number must be a valid phone number.',

            // Employment - Department (required for role level >= 4)
            'department_ids.required' => 'Department is required for this role level. Please select at least one department.',
            'department_ids.min'      => 'Please select at least one department.',

            'nationality.required' => 'Nationality is required.',
            'nationality.regex' => 'Nationality must be valid text (letters from any language, spaces, and standard punctuation).',
            'spouse_nationality.required_if' => 'Spouse nationality is required when marital status is Married.',
            'spouse_nationality.regex' => 'Spouse nationality must be valid text (letters from any language, spaces, and standard punctuation).',

            'dob.required' => 'Date of birth is required.',
            'dob.date' => 'Date of birth must be a valid date.',
            'dob.before' => 'Date of birth must be before today.',

            'cnic_expiry.after' => 'CNIC expiry date must be a future date.',
            'cnic_issue_date.required' => 'CNIC issue date is required.',
            'cnic_issue_date.date'     => 'CNIC issue date must be a valid date.',
            'cnic_issue_date.before_or_equal' => 'CNIC issue date cannot be in the future.',
            'cnic_issue_date.after'    => 'CNIC issue date must be after the date of birth.',

            'domicile_district.min' => 'Domicile district must be at least 2 characters.',
            'domicile_district.max' => 'Domicile district must not exceed 100 characters.',
            'domicile_district.regex' => 'District must include letters and may use numbers, spaces, and standard punctuation (no angle brackets).',

            'domicile_province.min' => 'Domicile province must be at least 2 characters.',
            'domicile_province.max' => 'Domicile province must not exceed 100 characters.',
            'domicile_province.regex' => 'Province must be valid text (letters from any language, spaces, and standard punctuation).',

            'city_of_birth.min' => 'Town / City of birth must be at least 2 characters.',
            'city_of_birth.max' => 'Town / City of birth must not exceed 50 characters.',
            
            'blood_group.regex' => 'The blood group format is invalid. It must be a standard format (e.g., A+, O-, AB+).',
            'city_of_birth.regex' => 'Town / City of birth must include letters and may use numbers, spaces, and standard punctuation (no angle brackets).',

            'religion.regex' => 'Religion must contain text only.',
            'sect.regex' => 'Sect must be letters and standard punctuation only (no digits).',

            'marital_status.required' => 'Marital status is required.',

            'spouse_name.regex' => 'Spouse name must contain letters (any language) and allowed punctuation — not numbers-only.',

            // Employment
            'organization_id.required' => 'Organization is required.',
            'organization_id.exists' => 'Selected organization does not exist.',

            'sbu_id.required' => 'SBU is required.',
            'sbu_id.exists' => 'Selected SBU does not exist.',

            'department_id.exists' => 'Selected department does not exist.',
            'department_ids.*.exists' => 'One or more selected departments are invalid for this SBU.',

            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'Selected role does not exist.',

            'employee_type.regex' => 'Employee type may only contain letters and standard punctuation.',
            'designation.regex' => 'Designation may only contain letters, spaces, and punctuation (like dot or hyphen).',
            'grade.max' => 'The grade field must not exceed 10 characters.',
            'grade.regex' => 'Grade may only contain letters, numbers, spaces, and hyphens.',
            'branch.regex' => 'Branch may only contain letters, numbers, spaces, and standard punctuation.',
            'location.regex' => 'Location must include letters and may use numbers, spaces, and standard punctuation (no angle brackets).',
            'vendor.regex' => 'Vendor may only contain letters, spaces, and standard punctuation.',

            'join_date.required' => 'Date of joining is required.',
            'join_date.date' => 'Date of joining must be a valid date.',
            'join_date.before_or_equal' => 'Date of joining cannot be in the future.',

            'biometric_id.regex' => 'Biometric ID may only contain letters, numbers, hyphens, and underscores.',

            'employment_category.required' => 'Resource type is required.',
            'employment_category.in' => 'The selected resource type is invalid.',
            'employee_status.required' => 'Employee status is required.',
            'employee_status.in' => 'The selected employee status is invalid.',
            'termination_reason.required' => 'Reason for termination is required when status is Terminated.',
            'termination_reason.min' => 'Reason for termination must be at least 5 characters.',
            'termination_reason.max' => 'Reason for termination must not exceed 500 characters.',
            'termination_reason.regex' => 'Reason for termination must not contain angle brackets.',
            'termination_date.required' => 'Date of termination is required when status is Terminated.',
            'termination_date.date' => 'Date of termination must be a valid date.',
            'termination_date.before_or_equal' => 'Date of termination cannot be in the future.',

            'suspension_reason.required' => 'Reason for suspension is required when status is Suspend.',
            'suspension_reason.max' => 'Reason for suspension must not exceed 500 characters.',
            'suspension_start_date.required' => 'Suspension start date is required when status is Suspend.',
            'suspension_start_date.date' => 'Suspension start date must be a valid date.',
            'suspension_end_date.required' => 'Suspension end date is required when status is Suspend.',
            'suspension_end_date.date' => 'Suspension end date must be a valid date.',
            'suspension_end_date.after_or_equal' => 'Suspension end date must be on or after the start date.',
            'assigned_floor_ids.array' => 'Assigned floors must be provided as a list.',
            'assigned_floor_ids.*.exists' => 'One or more selected floors are invalid for this SBU.',
            'intern_type.required' => 'Intern type is required when resource type is Intern.',
            'intern_duration.required' => 'Intern duration is required when resource type is Intern.',
            'employment_type.required' => 'Select Permanent or Contractual.',
            'employment_type.in' => 'The selected permanent or contractual option is invalid.',
            'contractual_type.required' => 'Contract type is required when Contractual is selected.',
            'contractual_type.in' => 'The selected contract type is invalid.',
            'contract_start_date.required' => 'Contract start date is required.',
            'contract_end_date.required' => 'Contract end date is required.',
            'probation_start_date.required' => 'Probation start date is required for employee resource type.',
            'probation_end_date.required' => 'Probation end date is required for employee resource type.',
            'probation_end_date.after_or_equal' => 'Probation end date must be on or after probation start date.',
            'employee_contract_start_date.required' => 'Contract start date is required for a time-bound contract.',
            'employee_contract_end_date.required' => 'Contract end date is required for a time-bound contract.',
            'contract_end_date.required' => 'Contract end date is required for a time-bound contract.',
            'contract_end_date.after_or_equal' => 'Contract end date must be on or after the start date.',
            'engagement_mode.required' => 'Work arrangement is required.',
            'engagement_mode.in' => 'The selected work arrangement is invalid.',
            'hybrid_days.required' => 'Select at least one weekday when work arrangement is Hybrid.',
            'hybrid_days.min' => 'Select at least one weekday when work arrangement is Hybrid.',
            'standard_schedule_mode.required' => 'Choose Default or Custom for standard office hours.',
            'standard_schedule_mode.in' => 'Standard schedule mode must be Default or Custom.',
            'working_days.required' => 'Select at least one working day for your custom schedule.',
            'working_days.min' => 'Select at least one working day for your custom schedule.',
            'working_days.*.in' => 'One or more selected working days are invalid.',
            'working_start_time.required' => 'Working start time is required for a custom standard schedule.',
            'working_start_time.date_format' => 'Working start time must be in HH:MM format.',
            'working_end_time.required' => 'Working end time is required for a custom standard schedule.',
            'working_end_time.date_format' => 'Working end time must be in HH:MM format.',
            'working_end_time.after' => 'Working end time must be after the start time.',
            'grace_period.integer' => 'Grace period must be a valid number.',
            'grace_period.min' => 'Grace period cannot be negative.',
            'grace_period.max' => 'Grace period cannot exceed 600 minutes.',
            'opening_grace_period.integer' => 'Grace period must be a valid number.',
            'opening_grace_period.min' => 'Grace period cannot be negative.',
            'opening_grace_period.max' => 'Grace period cannot exceed 600 minutes.',
            'closing_grace_period.integer' => 'Grace period must be a valid number.',
            'closing_grace_period.min' => 'Grace period cannot be negative.',
            'closing_grace_period.max' => 'Grace period cannot exceed 600 minutes.',

            // Police Verification
            'verification_status.required' => 'Verification status is required.',
            'msr_letter_no.required' => 'MSR letter number and date is required when status is Cleared or Not Cleared.',
            'msr_letter_no.regex' => 'MSR number must contain digits only.',
            'msr_date.before_or_equal' => 'MSR date cannot be in the future.',
            'addressee.required' => 'Addressee is required when status is Cleared or Not Cleared.',
            'addressee.min' => 'Addressee must be at least 2 characters.',
            'verifying_authority.required' => 'Verifying authority is required when status is Cleared or Not Cleared.',
            'verifying_authority.min' => 'Verifying authority must be at least 2 characters.',
            'verification_letter_no.required' => 'Verification letter number and date is required when status is Cleared or Not Cleared.',
            'verification_letter_date.required' => 'Verification letter date is required when status is Cleared or Not Cleared.',
            'verification_letter_date.after_or_equal' => 'Verification letter date must be on or after MSR date.',
            'verification_letter_date.before_or_equal' => 'Verification letter date cannot be in the future.',
            'next_verification_date.required' => 'Next verification date is required when status is Cleared or Not Cleared.',
            'next_verification_date.after' => 'Next verification date must be after verification letter date.',
            'police_remarks.required' => 'Remarks are required when status is Cleared or Not Cleared.',
            'police_remarks.min' => 'Remarks must be at least 2 characters when status is Cleared or Not Cleared.',
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
            'residence_phone.regex' => 'Residence phone number must contain only digits and may include a leading + sign. Length must be between 7 and 15 digits.',
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

            'branch_code.required' => 'Branch code is required.',
            'branch_code.regex' => 'Branch code may only contain letters, numbers, and hyphens (no spaces). Use a short code such as GL-102 or HQ01.',
            'banks.*.branch_code.regex' => 'Branch code may only contain letters, numbers, and hyphens (no spaces). Use a short code such as GL-102 or HQ01.',
            'branch_address.required' => 'Branch address is required.',
            'account_category.required' => 'Select whether this account is Personal or Company operated.',
            'account_category.in'       => 'Account category must be Personal or Company operated.',
            'bank_detail_id.exists'     => 'This bank account is no longer on file. Refresh the page and try again.',
            'is_salary_account.required' => 'Indicate whether this account is used for salary (payroll).',
            'branch_code.max' => 'Branch code must not exceed 10 characters.',
            'iban.required' => 'IBAN is required.',
            'iban.regex' => 'IBAN must contain letters and digits only (no spaces).',
            'iban.max'   => 'IBAN must not exceed 34 characters.',

            'banks.required' => 'Save bank accounts using Save account.',
            'banks.min' => 'Save at least two bank accounts: one Personal and one Company operated.',
            'banks.*.account_category.required' => 'Select account category for each bank account.',
            'banks.*.account_category.in' => 'Account category must be Personal or Company operated.',
            'banks.*.account_title.required' => 'Account title is required for each bank account.',
            'banks.*.bank_name.required' => 'Bank name is required for each bank account.',
            'banks.*.bank_name.regex' => 'Enter the real bank name (letters required). Numbers-only or account-style values are not accepted.',
            'bank_name.regex' => 'Enter the real bank name (letters required). Numbers-only or account-style values are not accepted.',
            'banks.*.branch_code.required' => 'Branch code is required for each bank account.',
            'banks.*.branch_code.max' => 'Branch code must not exceed 10 characters.',
            'banks.*.branch_address.required' => 'Branch address is required for each bank account.',
            'banks.*.iban.required' => 'IBAN is required for each bank account.',
            'banks.*.iban.regex' => 'IBAN must contain letters and digits only (no spaces).',
            'banks.*.iban.max' => 'IBAN must not exceed 34 characters.',
            'banks.*.account_no.required' => 'Account number is required for each bank account.',
            'banks.*.account_type.required' => 'A/C type is required for each bank account.',
            'banks.*.is_salary_account.required' => 'Each saved bank must indicate whether it is the salary account.',

            // Family
            'family.*.name.required_with' => 'Family member name is required.',
            'family.*.name.min' => 'Family member name must be at least 2 characters.',
            'family.*.name.max' => 'Family member name must not exceed 255 characters.',
            'family.*.name.regex' => 'Family member name contains invalid characters.',
            'family.*.gender.required_with' => 'Family member gender is required.',
            'family.*.dob.required_with' => 'Family member date of birth is required.',
            'family.*.dob.before' => 'Family member date of birth must be before today.',
            'family.*.relation.required_with' => 'Family member relation is required.',
            'family.*.nok_contact.max' => 'NOK contact must not exceed 15 digits.',
            'family.*.nok_contact.regex' => 'NOK contact must be a valid phone number (11 to 15 digits).',

            'family.*.occupation.regex' => 'Family member occupation contains invalid characters.',

            // Academics
            'academics.*.degree.required_with' => 'Degree type is required.',
            'academics.*.degree.max' => 'Degree type must not exceed 50 characters.',
            'academics.*.degree_title.required_with' => 'Degree title is required.',
            'academics.*.degree_title.max' => 'Degree title must not exceed 100 characters.',
            'academics.*.grade_cgpa.required_with' => 'Grade / CGPA is required.',
            'academics.*.grade_cgpa.max' => 'Grade / CGPA must not exceed 20 characters.',
            'academics.*.start_date.required_with' => 'Academic start date is required.',
            'academics.*.start_date.date' => 'Academic start date must be a valid date.',
            'academics.*.start_date.before_or_equal' => 'Academic start date cannot be in the future.',
            'academics.*.end_date.required_with' => 'Academic end date is required.',
            'academics.*.end_date.date' => 'Academic end date must be a valid date.',
            'academics.*.end_date.after_or_equal' => 'Academic end date must be on or after the start date.',

            'academics.*.field_of_study.max' => 'Field of study must not exceed 50 characters.',
            'academics.*.field_of_study.regex' => 'Field of study contains invalid characters.',
            'academics.*.institute.max' => 'Institute name must not exceed 255 characters.',

            // Previous Employments
            'employments.*.organization.required_with' => 'Previous employment organization name is required.',
            'employments.*.organization.max' => 'Previous employment organization must not exceed 255 characters.',
            'employments.*.designation.required_with' => 'Previous employment designation is required.',
            'employments.*.designation.max' => 'Employment history row #:position: designation must not exceed 255 characters.',
            'employments.*.from_date.required_with' => 'Previous employment from date is required.',
            'employments.*.from_date.date' => 'Previous employment from date must be a valid date.',
            'employments.*.from_date.before_or_equal' => 'Previous employment from date cannot be in the future.',
            'employments.*.to_date.required_with' => 'Previous employment to date is required.',
            'employments.*.to_date.date' => 'Previous employment to date must be a valid date.',
            'employments.*.to_date.after_or_equal' => 'Previous employment to date must be on or after from date.',

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
            'ref1_relationship.in' => 'Reference 1 relationship must be one of: Family, Friend, Colleague, Academic, Professional, Other.',
            'ref2_relationship.in' => 'Reference 2 relationship must be one of: Family, Friend, Colleague, Academic, Professional, Other.',

            // Files
            'profile_photo.mimes' => 'Profile photo must be a JPG, JPEG, PNG, GIF, or SVG file.',
            'profile_photo.max'   => 'Profile photo must be at most 20MB.',

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
            'attachments.*.files.*.mimes' => 'Attachment file must be of type: jpg, jpeg, png, pdf, doc, docx, xls, xlsx, or txt.',
            'attachments.*.files.*.max' => 'Each attachment file must not exceed 20 MB.',

            // Password
            'password.required_if' => 'Password is required when creating a user account.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 64 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',

            // Subsection rows
            'start_date.required' => 'Start date is required.',
            'start_date.date'     => 'Start date must be a valid date.',
            'start_date.before_or_equal' => 'Start date cannot be in the future.',
            'end_date.required'   => 'End date is required.',
            'end_date.date'       => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',
            'from_date.required'  => 'From date is required.',
            'from_date.date'      => 'From date must be a valid date.',
            'from_date.before_or_equal' => 'From date cannot be in the future.',
            'to_date.required'    => 'To date is required.',
            'to_date.date'        => 'To date must be a valid date.',
            'to_date.after_or_equal' => 'To date must be on or after from date.',
        ];
    }

    protected function referenceRelationshipValues(): array
    {
        return ['Family', 'Friend', 'Colleague', 'Academic', 'Professional', 'Other'];
    }
}

