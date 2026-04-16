<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeIdSequence;
use App\Models\EmployeePoliceVerification;
use App\Models\EmployeeArmedForce;
use App\Models\EmployeeContact;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeFamilyMember;
use App\Models\EmployeeAcademic;
use App\Models\EmployeeExEmployment;
use App\Models\EmployeeMedical;
use App\Models\EmployeeReference;
use App\Models\MediaFile;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Sbu;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditTrailService;
use App\Services\EmployeeEmploymentInformationService;
use App\Services\EmployeeGeneralInformationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    protected $auditTrailService;

    protected EmployeeGeneralInformationService $generalInformation;

    protected EmployeeEmploymentInformationService $employmentInformation;

    public function __construct(
        AuditTrailService $auditTrailService,
        EmployeeGeneralInformationService $generalInformation,
        EmployeeEmploymentInformationService $employmentInformation
    ) {
        $this->auditTrailService   = $auditTrailService;
        $this->generalInformation  = $generalInformation;
        $this->employmentInformation = $employmentInformation;
    }

    public function index(): View
    {
        $organizations = Organization::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->with([
                'sbus' => static function ($query): void {
                    $query->select(['id', 'organization_id', 'name'])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
                'sbus.departments' => static function ($query): void {
                    $query->select(['id', 'sbu_id', 'name'])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->select(['id', 'name', 'sbu_id', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $sbus = Sbu::query()
            ->select(['id', 'name', 'organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.employee.index', compact('organizations', 'departments', 'sbus'));
    }

    public function getFormData(): array
    {
        $organizations = Organization::query()
            ->select([
                'id',
                'name',
                'working_days',
                'working_start_time',
                'working_end_time',
                'opening_grace_period',
                'closing_grace_period',
            ])
            ->where('is_active', true)
            ->with([
                'sbus' => static function ($query): void {
                    $query->select([
                        'id',
                        'organization_id',
                        'name',
                        'working_days',
                        'working_start_time',
                        'working_end_time',
                        'opening_grace_period',
                        'closing_grace_period',
                    ])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
                'sbus.departments' => static function ($query): void {
                    $query->select([
                        'id',
                        'sbu_id',
                        'name',
                        'working_days',
                        'working_start_time',
                        'working_end_time',
                        'opening_grace_period',
                        'closing_grace_period',
                    ])
                        ->where('is_active', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->select(['id', 'name', 'organization_id', 'department_id', 'sbu_id', 'slug'])
            ->where('is_active', true)
            ->with([
                'department:id,sbu_id',
                'sbus:id',
            ])
            ->orderBy('name')
            ->get();

        $orgsData = $organizations->map(fn ($o) => [
            'id' => $o->id,
            'name'                   => $o->name,
            'working_days'           => $o->working_days,
            'working_start_time'     => $o->working_start_time,
            'working_end_time'       => $o->working_end_time,
            'opening_grace_period'   => $o->opening_grace_period,
            'closing_grace_period'   => $o->closing_grace_period,
            'sbus'                   => $o->sbus->map(fn ($s) => [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'working_days'         => $s->working_days,
                'working_start_time'   => $s->working_start_time,
                'working_end_time'     => $s->working_end_time,
                'opening_grace_period' => $s->opening_grace_period,
                'closing_grace_period' => $s->closing_grace_period,
                'departments'          => $s->departments->map(fn ($d) => [
                    'id'                   => $d->id,
                    'name'                 => $d->name,
                    'working_days'         => $d->working_days,
                    'working_start_time'   => $d->working_start_time,
                    'working_end_time'     => $d->working_end_time,
                    'opening_grace_period' => $d->opening_grace_period,
                    'closing_grace_period' => $d->closing_grace_period,
                ])->values()->all(),
            ])->values()->all(),
        ])->values()->all();

        $rolesData = $roles->map(function ($r) {
            $linked = $r->sbus->pluck('id');
            if ($r->sbu_id) {
                $linked->push($r->sbu_id);
            }
            if ($r->department?->sbu_id) {
                $linked->push($r->department->sbu_id);
            }

            return [
                'id'                    => $r->id,
                'name'                  => $r->name,
                'organization_id'       => $r->organization_id,
                'sbu_id'                => $r->sbu_id,
                'department_id'         => $r->department_id,
                'linked_sbu_ids'        => $linked->unique()->values()->all(),
                'is_organization_level' => $r->isOrganizationLevelRole(),
            ];
        })->values()->all();

        return compact('organizations', 'orgsData', 'rolesData');
    }

    public function store(array $data, array $files = [], array $attachments = []): Employee
    {
        return DB::transaction(function () use ($data, $files, $attachments) {
            $role = Role::find($data['role_id'] ?? 0);
            $code = null;
            $orgLevel = false;

            if ($role) {
                $orgLevel = $role->isOrganizationLevelRole();
                if ($orgLevel) {
                    $sbuForCode = Sbu::where('organization_id', (int) ($data['organization_id'] ?? 0))->orderBy('id')->value('id');
                    if (!$sbuForCode) {
                        throw new \InvalidArgumentException('No SBU found under organization for employee code generation.');
                    }
                    $code = $this->generateNextCode((int) $sbuForCode);
                } else {
                    $sbuId = isset($data['sbu_id']) ? (int) $data['sbu_id'] : null;
                    if (!$sbuId) {
                        throw new \InvalidArgumentException('SBU is required to generate employee code.');
                    }
                    $code = $this->generateNextCode($sbuId);
                }
            }

            $scheduleAttrs = $this->employmentInformation->standardScheduleAttributesForPersist($data, $role, $orgLevel);

            $employee = Employee::create([
                'full_name'           => $data['full_name'],
                'father_name'         => $data['father_name'] ?? null,
                'employee_code'       => $code,
                'organization_id'     => $data['organization_id'] ?? null,
                'sbu_id'              => $data['sbu_id'] ?? null,
                'department_id'       => $data['department_id'] ?? null,
                'department_ids'      => $data['department_ids'] ?? null,
                'role_id'             => $data['role_id'] ?? null,
                'employee_type'       => $data['employee_type'] ?? null,
                'employment_type'     => $data['employment_type'] ?? null,
                'designation'         => $data['designation'] ?? null,
                'grade'               => $data['grade'] ?? null,
                'branch'              => $data['branch'] ?? null,
                'location'            => $data['location'] ?? null,
                'email'               => $data['email'] ?? $data['contact_email'] ?? null,
                'phone'               => $data['phone'] ?? null,
                'cnic'                => $data['cnic'] ?? null,
                'cnic_expiry'         => !empty($data['cnic_expiry']) ? $data['cnic_expiry'] : null,
                'father_cnic'         => $data['father_cnic'] ?? null,
                'ntn'                 => $data['ntn'] ?? null,
                'gender'              => $data['gender'] ?? null,
                'nationality'         => $data['nationality'] ?? null,
                'dob'                 => !empty($data['dob']) ? $data['dob'] : null,
                'domicile_district'   => $data['domicile_district'] ?? null,
                'domicile_province'   => $data['domicile_province'] ?? null,
                'city_of_birth'       => $data['city_of_birth'] ?? null,
                'religion'            => $data['religion'] ?? null,
                'sect'                => $data['sect'] ?? null,
                'marital_status'      => $data['marital_status'] ?? null,
                'spouse_name'         => $data['spouse_name'] ?? null,
                'spouse_cnic'         => $data['spouse_cnic'] ?? null,
                'spouse_nationality'  => $data['spouse_nationality'] ?? null,
                'nok_name'            => $data['nok_name'] ?? null,
                'nok_cnic'            => $data['nok_cnic'] ?? null,
                'nok_cnic_expiry_date' => !empty($data['nok_cnic_expiry_date']) ? $data['nok_cnic_expiry_date'] : null,
                'nok_relation'        => $data['nok_relation'] ?? null,
                'nok_dob'             => !empty($data['nok_dob']) ? $data['nok_dob'] : null,
                'nok_contact'         => $data['nok_contact'] ?? null,
                'is_ex_armed_force'   => ! empty($data['is_ex_armed_force']),
                'is_father_deceased'  => ! empty($data['is_father_deceased']),
                'site'                => $data['site'] ?? null,
                'join_date'           => !empty($data['join_date']) ? $data['join_date'] : null,
                'floor_access'        => isset($data['floor_access']) ? (bool) $data['floor_access'] : false,
                'biometric_id'        => $data['biometric_id'] ?? null,
                'employment_category' => $data['employment_category'] ?? null,
                'intern_type'         => $data['intern_type'] ?? null,
                'intern_duration'     => $data['intern_duration'] ?? null,
                'contractual_type'    => $data['contractual_type'] ?? null,
                'contract_start_date' => ! empty($data['contract_start_date']) ? $data['contract_start_date'] : (!empty($data['employee_contract_start_date']) ? $data['employee_contract_start_date'] : null),
                'contract_end_date'   => ! empty($data['contract_end_date']) ? $data['contract_end_date'] : (!empty($data['employee_contract_end_date']) ? $data['employee_contract_end_date'] : null),
                'engagement_mode'     => $data['engagement_mode'] ?? null,
                'hybrid_days'         => $data['hybrid_days'] ?? null,
                'standard_schedule_mode' => $scheduleAttrs['standard_schedule_mode'] ?? null,
                'working_days'        => $scheduleAttrs['working_days'] ?? null,
                'working_start_time'  => $scheduleAttrs['working_start_time'] ?? null,
                'working_end_time'    => $scheduleAttrs['working_end_time'] ?? null,
                'opening_grace_period' => $scheduleAttrs['opening_grace_period'] ?? null,
                'closing_grace_period' => $scheduleAttrs['closing_grace_period'] ?? null,
                'sync_with_biometric' => isset($data['sync_with_biometric']) ? (bool) $data['sync_with_biometric'] : false,
                'is_active'           => true,
            ]);

            $this->savePoliceVerification($employee->id, $data);
            if (! empty($data['is_ex_armed_force'])) {
                $this->saveArmedForce($employee->id, $data);
            }
            $this->saveContact($employee->id, $data);
            $this->saveBankDetails($employee->id, $data['banks'] ?? []);
            $this->saveFamilyMembers($employee->id, $data['family'] ?? []);
            $this->saveAcademics($employee->id, $data['academics'] ?? []);
            $this->saveExEmployments($employee->id, $data['employments'] ?? []);
            $this->saveMedical($employee->id, $data);
            $this->saveReferences($employee->id, $data);
            $this->saveMediaFiles($employee->id, $files);
            $this->saveAttachmentFiles($employee->id, $attachments);

            if (!empty($data['create_user_account'])) {
                $this->createUserAccount($employee, $data);
            }

            Log::info('Employee created', ['id' => $employee->id, 'code' => $code]);

            $this->auditTrailService->log(
                action: 'created',
                category: 'Employee',
                description: "New employee {$employee->full_name} ({$code}) was registered.",
                auditable: $employee
            );

            return $employee;
        });
    }

    public function previewNextEmployeeCode(int $organizationId, int $roleId, ?int $sbuId = null): string
    {
        $role = Role::query()->find($roleId);
        if (! $role) {
            throw new \InvalidArgumentException('Invalid role.');
        }
        if ((int) ($role->organization_id ?? 0) !== $organizationId) {
            throw new \InvalidArgumentException('Role does not belong to the selected organization.');
        }
        $orgLevel = $role->isOrganizationLevelRole();
        if ($orgLevel) {
            $sbuForCode = Sbu::query()->where('organization_id', $organizationId)->orderBy('id')->value('id');
            if (! $sbuForCode) {
                throw new \InvalidArgumentException('No SBU found under organization for employee code generation.');
            }

            return $this->peekNextCode((int) $sbuForCode);
        }
        if (! $sbuId) {
            throw new \InvalidArgumentException('SBU is required to preview employee number.');
        }
        $sbu = Sbu::query()->find($sbuId);
        if (! $sbu || (int) $sbu->organization_id !== $organizationId) {
            throw new \InvalidArgumentException('Invalid SBU for the selected organization.');
        }

        return $this->peekNextCode($sbuId);
    }

    private function peekNextCode(int $sbuId): string
    {
        $prefix = $this->buildSbuPrefix($sbuId);
        $seq    = EmployeeIdSequence::where('sbu_id', $sbuId)->first();
        $last   = $seq ? $seq->last_number : 100;

        // Sync with highest existing code so peek is accurate
        $maxExisting = Employee::whereNotNull('employee_code')
            ->where('sbu_id', $sbuId)
            ->where('employee_code', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(employee_code, "-", -1) AS UNSIGNED) DESC')
            ->value('employee_code');

        if ($maxExisting) {
            $maxNum = (int) substr($maxExisting, strrpos($maxExisting, '-') + 1);
            if ($maxNum >= $last) {
                $last = $maxNum;
            }
        }

        return $prefix . '-' . ($last + 1);
    }

    private function generateNextCode(int $sbuId): string
    {
        $prefix = $this->buildSbuPrefix($sbuId);
        $seq = EmployeeIdSequence::where('sbu_id', $sbuId)->lockForUpdate()->first();

        if (!$seq) {
            // No sequence record — create one seeded from existing data
            $maxExisting = Employee::whereNotNull('employee_code')
                ->where('sbu_id', $sbuId)
                ->where('employee_code', 'like', $prefix . '-%')
                ->orderByRaw('CAST(SUBSTRING_INDEX(employee_code, "-", -1) AS UNSIGNED) DESC')
                ->value('employee_code');
            $lastNum = $maxExisting
                ? (int) substr($maxExisting, strrpos($maxExisting, '-') + 1)
                : 100;
            $seq = EmployeeIdSequence::create(['sbu_id' => $sbuId, 'prefix' => $prefix, 'last_number' => $lastNum]);
        }

        $prefix = strtoupper($prefix);
        if ($seq->prefix !== $prefix) {
            $seq->prefix = $prefix;
            $seq->save();
        }

        // Self-heal: if the sequence is behind the highest existing code, catch up first
        $maxExisting = Employee::whereNotNull('employee_code')
            ->where('sbu_id', $sbuId)
            ->where('employee_code', 'like', $prefix . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(employee_code, "-", -1) AS UNSIGNED) DESC')
            ->value('employee_code');

        if ($maxExisting) {
            $maxNum = (int) substr($maxExisting, strrpos($maxExisting, '-') + 1);
            if ($maxNum >= $seq->last_number) {
                $seq->last_number = $maxNum;
                $seq->save();
            }
        }

        $seq->increment('last_number');
        $seq->refresh();

        return $prefix . '-' . $seq->last_number;
    }

    private function buildSbuPrefix(int $sbuId): string
    {
        $sbu = Sbu::find($sbuId);
        if (!$sbu || empty($sbu->name)) {
            return 'SBU';
        }

        $stopWords = ['THE', 'AND', 'OF', 'IN', 'ON', 'AT', 'FOR', 'TO', 'A', 'AN', 'MALL'];
        $words = preg_split('/\s+/', trim((string) $sbu->name)) ?: [];
        $letters = [];

        foreach ($words as $word) {
            $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $word) ?? '');
            if ($clean === '' || in_array($clean, $stopWords, true)) {
                continue;
            }
            $letters[] = $clean[0];
        }

        if (empty($letters)) {
            return 'SBU';
        }

        return substr(implode('', $letters), 0, 4);
    }

    public function savePoliceVerification(int $id, array $d): void
    {
        if (empty($d['verification_status']) && empty($d['msr_letter_no'])) return;
        EmployeePoliceVerification::updateOrCreate(
            ['employee_id' => $id],
            [
                'verification_status'    => $d['verification_status'] ?? null,
                'msr_letter_no'          => $d['msr_letter_no'] ?? null,
                'msr_date'               => !empty($d['msr_date']) ? $d['msr_date'] : null,
                'addressee'              => $d['addressee'] ?? null,
                'verifying_authority'    => $d['verifying_authority'] ?? null,
                'verification_letter_no' => $d['verification_letter_no'] ?? null,
                'verification_letter_date' => !empty($d['verification_letter_date']) ? $d['verification_letter_date'] : null,
                'next_verification_date' => !empty($d['next_verification_date']) ? $d['next_verification_date'] : null,
                'remarks'                => $d['police_remarks'] ?? null,
            ]
        );
    }

    public function saveArmedForce(int $id, array $d): void
    {
        if (empty($d['service_no']) && empty($d['rank'])) return;
        EmployeeArmedForce::updateOrCreate(
            ['employee_id' => $id],
            [
                'service_no'            => $d['service_no'] ?? null,
                'rank'                  => $d['rank'] ?? null,
                'medical_category'      => $d['medical_category'] ?? null,
                'date_of_commissioning' => !empty($d['date_of_commissioning']) ? $d['date_of_commissioning'] : null,
                'date_of_retirement'    => !empty($d['date_of_retirement']) ? $d['date_of_retirement'] : null,
                'reason_of_retirement'  => $d['reason_of_retirement'] ?? null,
                'corps_regiment'        => $d['corps_regiment'] ?? null,
                'ex_army_unit'          => $d['ex_army_unit'] ?? null,
                'trade'                 => $d['trade'] ?? null,
                'pma_lc_ots'            => $d['pma_lc_ots'] ?? null,
            ]
        );
    }

    public function saveContact(int $id, array $d): void
    {
        if (empty($d['residence_phone']) && empty($d['cell_no']) && empty($d['present_address'])) return;
        EmployeeContact::updateOrCreate(
            ['employee_id' => $id],
            [
                'residence_phone'   => $d['residence_phone'] ?? null,
                'emergency_contact' => $d['emergency_contact'] ?? null,
                'cell_no'           => $d['cell_no'] ?? null,
                'email'             => $d['contact_email'] ?? null,
                'present_address'   => $d['present_address'] ?? null,
                'permanent_address' => $d['permanent_address'] ?? null,
            ]
        );
    }

    public function saveBankDetails(int $id, array $rows): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (empty($row['account_title']) && empty($row['account_no'])) {
                continue;
            }
            EmployeeBankDetail::create([
                'employee_id'        => $id,
                'account_category'   => $row['account_category'] ?? null,
                'account_title'      => $row['account_title'] ?? null,
                'account_no'         => $row['account_no'] ?? null,
                'bank_name'          => $row['bank_name'] ?? null,
                'branch_code'        => $row['branch_code'] ?? null,
                'branch_address'     => $row['branch_address'] ?? null,
                'iban'               => ! empty($row['iban']) ? $row['iban'] : null,
                'account_type'       => $row['account_type'] ?? null,
                'is_salary_account'  => ! empty($row['is_salary_account']),
            ]);
        }
    }

    public function saveBankDetailRow(int $employeeId, array $row, ?int $bankDetailId = null): EmployeeBankDetail
    {
        $wantsSalary = ! empty($row['is_salary_account']);
        $payload = [
            'employee_id'       => $employeeId,
            'account_category'  => $row['account_category'] ?? null,
            'account_title'     => $row['account_title'] ?? null,
            'account_no'        => $row['account_no'] ?? null,
            'bank_name'         => $row['bank_name'] ?? null,
            'branch_code'       => $row['branch_code'] ?? null,
            'branch_address'    => $row['branch_address'] ?? null,
            'iban'              => ! empty($row['iban']) ? $row['iban'] : null,
            'account_type'      => $row['account_type'] ?? null,
            'is_salary_account' => $wantsSalary,
        ];

        if ($bankDetailId) {
            $bank = EmployeeBankDetail::query()
                ->where('employee_id', $employeeId)
                ->where('id', $bankDetailId)
                ->first();
            if (! $bank) {
                throw new \InvalidArgumentException('Bank account not found.');
            }
            $bank->update($payload);
            $bank = $bank->fresh();
        } else {
            $bank = EmployeeBankDetail::create($payload);
        }

        $this->normalizeEmployeeBankSalaryFlags($employeeId, (int) $bank->id, $wantsSalary);

        return $bank->fresh();
    }

    public function deleteBankDetailRow(int $employeeId, int $bankDetailId): bool
    {
        $row = EmployeeBankDetail::query()
            ->where('employee_id', $employeeId)
            ->where('id', $bankDetailId)
            ->first();
        if (! $row) {
            return false;
        }
        $row->delete();

        return true;
    }

    public function salaryBankIdForEmployee(int $employeeId): ?int
    {
        return EmployeeBankDetail::query()
            ->where('employee_id', $employeeId)
            ->where('is_salary_account', true)
            ->value('id');
    }

    private function normalizeEmployeeBankSalaryFlags(int $employeeId, int $subjectBankId, bool $subjectWantsSalary): void
    {
        if ($subjectWantsSalary) {
            EmployeeBankDetail::query()
                ->where('employee_id', $employeeId)
                ->where('id', '!=', $subjectBankId)
                ->update(['is_salary_account' => false]);
            EmployeeBankDetail::query()
                ->where('id', $subjectBankId)
                ->update(['is_salary_account' => true]);

            return;
        }

        EmployeeBankDetail::query()
            ->where('id', $subjectBankId)
            ->update(['is_salary_account' => false]);
    }

    private function saveFamilyMembers(int $id, array $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['name'])) continue;
            EmployeeFamilyMember::create([
                'employee_id' => $id,
                'name'        => $row['name'],
                'gender'      => $row['gender'] ?? null,
                'dob'         => !empty($row['dob']) ? $row['dob'] : null,
                'relation'    => $row['relation'] ?? null,
                'occupation'  => $row['occupation'] ?? null,
            ]);
        }
    }

    private function saveAcademics(int $id, array $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['degree'])) continue;
            EmployeeAcademic::create([
                'employee_id'    => $id,
                'degree'         => $row['degree'],
                'grade_cgpa'     => $row['grade_cgpa'] ?? null,
                'start_date'     => !empty($row['start_date']) ? $row['start_date'] : null,
                'end_date'       => !empty($row['end_date']) ? $row['end_date'] : null,
                'field_of_study' => $row['field_of_study'] ?? null,
                'institute'      => $row['institute'] ?? null,
            ]);
        }
    }

    private function saveExEmployments(int $id, array $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['organization'])) continue;
            EmployeeExEmployment::create([
                'employee_id'        => $id,
                'organization'       => $row['organization'],
                'designation'        => $row['designation'] ?? null,
                'from_date'          => !empty($row['from_date']) ? $row['from_date'] : null,
                'to_date'            => !empty($row['to_date']) ? $row['to_date'] : null,
                'salary'             => $row['salary'] ?? null,
                'reason_for_leaving' => $row['reason_for_leaving'] ?? null,
            ]);
        }
    }

    public function saveMedical(int $id, array $d): void
    {
        if (empty($d['last_fitness_test']) && empty($d['blood_group'])) return;
        EmployeeMedical::updateOrCreate(
            ['employee_id' => $id],
            [
                'last_fitness_test'      => $d['last_fitness_test'] ?? null,
                'has_disability'         => $d['has_disability'] ?? null,
                'blood_group'            => $d['blood_group'] ?? null,
                'disability_type'        => $d['disability_type'] ?? null,
                'disability_description' => $d['disability_description'] ?? null,
            ]
        );
    }

    public function savePhoto(int $id, $file): void
    {
        // Remove existing photo first to ensure only one profile photo exists
        $this->deletePhoto($id);
        $this->saveMediaFiles($id, [$file]);
    }

    public function deletePhoto(int $id): bool
    {
        $photo = MediaFile::where('module_name', 'employee')
            ->where('module_id', $id)
            ->where('file_type', 'photo')
            ->first();

        if ($photo) {
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }
            return $photo->delete();
        }
        return false;
    }

    public function saveReferences(int $id, array $d): void
    {
        for ($i = 1; $i <= 2; $i++) {
            if (empty($d["ref{$i}_name"])) continue;
            EmployeeReference::updateOrCreate(
                ['employee_id' => $id, 'ref_number' => $i],
                [
                    'name'         => $d["ref{$i}_name"],
                    'designation'  => $d["ref{$i}_designation"] ?? null,
                    'organization' => $d["ref{$i}_organization"] ?? null,
                    'contact_no'   => $d["ref{$i}_contact"] ?? null,
                    'relationship' => $d["ref{$i}_relationship"] ?? null,
                ]
            );
        }
    }

    public function saveFamilyMember(int $id, array $row)
    {
        if (empty($row['name'])) return null;
        return EmployeeFamilyMember::create([
            'employee_id' => $id,
            'name'        => $row['name'],
            'gender'      => $row['gender'] ?? null,
            'dob'         => !empty($row['dob']) ? $row['dob'] : null,
            'relation'    => $row['relation'] ?? null,
            'occupation'  => $row['occupation'] ?? null,
        ]);
    }

    public function saveAcademic(int $id, array $row)
    {
        if (empty($row['degree'])) return null;
        return EmployeeAcademic::create([
            'employee_id'    => $id,
            'degree'         => $row['degree'],
            'grade_cgpa'     => $row['grade_cgpa'] ?? null,
            'start_date'     => !empty($row['start_date']) ? $row['start_date'] : null,
            'end_date'       => !empty($row['end_date']) ? $row['end_date'] : null,
            'field_of_study' => $row['field_of_study'] ?? null,
            'institute'      => $row['institute'] ?? null,
        ]);
    }

    public function saveExEmployment(int $id, array $row)
    {
        if (empty($row['organization'])) return null;
        return EmployeeExEmployment::create([
            'employee_id'        => $id,
            'organization'       => $row['organization'],
            'designation'        => $row['designation'] ?? null,
            'from_date'          => !empty($row['from_date']) ? $row['from_date'] : null,
            'to_date'            => !empty($row['to_date']) ? $row['to_date'] : null,
            'salary'             => $row['salary'] ?? null,
            'reason_for_leaving' => $row['reason_for_leaving'] ?? null,
        ]);
    }

    public function deleteSubsectionRow(string $type, int $id): bool
    {
        switch ($type) {
            case 'family_row':
                return EmployeeFamilyMember::where('id', $id)->delete() > 0;
            case 'academic_row':
                return EmployeeAcademic::where('id', $id)->delete() > 0;
            case 'employment_row':
                return EmployeeExEmployment::where('id', $id)->delete() > 0;
            default:
                return false;
        }
    }

    private function saveMediaFiles(int $id, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store("employees/{$id}", 'public');
            MediaFile::create([
                'module_name' => 'employee',
                'module_id'   => $id,
                'file_type'   => 'photo',
                'file_path'   => $path,
                'file_name'   => $file->getClientOriginalName(),
                'mime_type'   => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function saveAttachmentFiles(int $id, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $name = $attachment['name'] ?? null;
            $type = $attachment['type'] ?? null;
            $description = $attachment['description'] ?? null;
            $files = $attachment['files'] ?? [];

            foreach ($files as $file) {
                $path = $file->store("employees/{$id}/attachments", 'public');
                MediaFile::create([
                    'module_name'     => 'employee',
                    'module_id'       => $id,
                    'file_type'       => 'attachment',
                    'attachment_type' => $type ?: null,
                    'title'           => $name ?: null,
                    'description'     => $description ?: null,
                    'file_path'       => $path,
                    'file_name'       => $file->getClientOriginalName(),
                    'mime_type'       => $file->getMimeType(),
                    'uploaded_by'     => Auth::id(),
                ]);
            }
        }
    }

    public function saveSingleAttachment(int $id, array $attachmentData): array
    {
        $savedFiles = [];
        $files = $attachmentData['files'] ?? [];
        foreach ($files as $file) {
            $path = $file->store("employees/{$id}/attachments", 'public');
            $savedFile = MediaFile::create([
                'module_name'     => 'employee',
                'module_id'       => $id,
                'file_type'       => 'attachment',
                'attachment_type' => $attachmentData['type'] ?: null,
                'title'           => $attachmentData['name'] ?: null,
                'description'     => $attachmentData['description'] ?: null,
                'file_path'       => $path,
                'file_name'       => $file->getClientOriginalName(),
                'mime_type'       => $file->getMimeType(),
                'uploaded_by'     => Auth::id(),
            ]);
            $savedFiles[] = $savedFile;
        }
        return $savedFiles;
    }

    public function deleteAttachment(int $id): bool
    {
        $attachment = MediaFile::where('file_type', 'attachment')->find($id);

        if ($attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            return $attachment->delete();
        }
        return false;
    }

    private function createUserAccount(Employee $employee, array $data): void
    {
        if (!$employee->email || User::where('email', $employee->email)->exists()) return;
        User::create([
            'name'        => $employee->full_name,
            'email'       => $employee->email,
            'password'    => Hash::make($data['password'] ?? 'Welcome@123'),
            'employee_id' => $employee->id,
        ]);
    }

    public function getTableData(array $filters = []): array
    {
        $query = Employee::query()
            ->select([
                'id',
                'full_name',
                'employee_code',
                'employment_category',
                'cnic',
                'nationality',
                'gender',
                'join_date',
                'designation',
                'employment_type',
                'biometric_id',
                'sync_with_biometric',
                'site',
                'floor_access',
                'is_active',
                'email',
                'organization_id',
                'sbu_id',
                'department_id',
                'department_ids',
                'role_id',
            ])
            ->with([
                'department:id,name',
                'organization:id,name',
                'sbu:id,name',
                'role:id,name',
                'mediaFiles' => static function ($q): void {
                    $q->select(['id', 'module_id', 'file_type', 'file_path', 'file_name'])
                        ->where('module_name', 'employee')
                        ->where('file_type', 'photo');
                },
                'policeVerification:id,employee_id,verification_status',
                'contact:id,employee_id,email,cell_no',
            ])
            ->orderByDesc('id');

        $type = $filters['filter_employee_type'] ?? null;
        if (!empty($type)) {
            if ($type === 'Third-party') {
                $query->where('employment_type', 'Third-party');
            } elseif ($type === 'Internal') {
                $query->where(function ($q) {
                    $q->whereNull('employment_type')
                        ->orWhere('employment_type', '!=', 'Third-party');
                });
            }
        }

        if (!empty($filters['filter_organization'])) {
            $orgName = $filters['filter_organization'];
            $query->whereIn('organization_id', Organization::query()
                ->where('name', $orgName)
                ->select('id'));
        }

        if (!empty($filters['filter_sbu'])) {
            $sbuName = $filters['filter_sbu'];
            $query->whereIn('sbu_id', Sbu::query()
                ->where('name', $sbuName)
                ->select('id'));
        }

        if (!empty($filters['filter_department'])) {
            $departmentName = $filters['filter_department'];
            $departmentIds = Department::query()
                ->where('name', $departmentName)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
            if ($departmentIds !== []) {
                $query->where(function ($q) use ($departmentIds): void {
                    $q->whereIn('department_id', $departmentIds);
                    foreach ($departmentIds as $deptId) {
                        $q->orWhereJsonContains('department_ids', $deptId);
                    }
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if (!empty($filters['filter_name'])) {
            $name = $filters['filter_name'];
            $query->where('full_name', 'like', '%' . $name . '%');
        }

        if (!empty($filters['filter_cnic'])) {
            $cnic = $filters['filter_cnic'];
            $query->where('cnic', 'like', '%' . $cnic . '%');
        }

        $employees = $query->get();

        $allDeptIds = [];
        foreach ($employees as $emp) {
            if (! empty($emp->department_id)) {
                $allDeptIds[] = (int) $emp->department_id;
            }
            $rawMulti = $emp->department_ids;
            if (! is_array($rawMulti)) {
                $rawMulti = $rawMulti !== null && $rawMulti !== '' ? [$rawMulti] : [];
            }
            foreach ($rawMulti as $did) {
                $allDeptIds[] = (int) $did;
            }
        }
        $allDeptIds = array_values(array_unique(array_filter($allDeptIds)));

        $deptRows = $allDeptIds === [] ? collect() : Department::query()
            ->whereIn('id', $allDeptIds)
            ->get(['id', 'name', 'sbu_id'])
            ->keyBy('id');

        $sbuIdsFromDepts = $deptRows->pluck('sbu_id')->filter()->map(fn ($id) => (int) $id)->values()->all();
        $sbuNameById = $sbuIdsFromDepts === [] ? [] : Sbu::query()
            ->whereIn('id', $sbuIdsFromDepts)
            ->pluck('name', 'id')
            ->toArray();

        return $employees->map(function (Employee $emp) use ($deptRows, $sbuNameById) {
            $rawDeptIds = $emp->department_ids;
            if (! is_array($rawDeptIds)) {
                $rawDeptIds = $rawDeptIds !== null && $rawDeptIds !== '' ? [$rawDeptIds] : [];
            }
            if (! empty($emp->department_id)) {
                $rawDeptIds[] = (int) $emp->department_id;
            }
            $deptIds = array_values(array_unique(array_filter(array_map('intval', $rawDeptIds))));
            $deptNames = [];
            $deptSbuNames = [];
            foreach ($deptIds as $deptId) {
                $dept = $deptRows->get($deptId);
                if (! $dept) {
                    continue;
                }
                if (! empty($dept->name)) {
                    $deptNames[] = $dept->name;
                }
                $sbuName = $sbuNameById[$dept->sbu_id] ?? null;
                if ($sbuName) {
                    $deptSbuNames[] = $sbuName;
                }
            }
            $deptNames = array_values(array_unique($deptNames));
            $deptSbuNames = array_values(array_unique($deptSbuNames));

            $initials    = $this->getInitials($emp->full_name ?? '');
            $biometricId = $emp->biometric_id;
            $syncStatus  = $biometricId
                ? ($emp->sync_with_biometric ? 'Synced' : 'Pending')
                : 'Not Linked';
            $employeeType = ($emp->employment_type === 'Third-party') ? 'Third-party' : 'Internal';

            $photo    = $emp->mediaFiles->where('file_type', 'photo')->first();
            $photoUrl = $photo ? Storage::url($photo->file_path) : null;

            $departmentLabel = $deptNames !== [] ? implode(', ', $deptNames) : ($emp->department?->name ?? '-');
            $sbuLabel = $emp->sbu?->name ?? ($deptSbuNames[0] ?? '-');

            return [
                'id'                  => $emp->id,
                'employee_code'       => $emp->employee_code ?? '-',
                'employment_category' => $emp->employment_category ?? '-',
                'photo_url'           => $photoUrl,
                'full_name'           => $emp->full_name ?? '-',
                'initials'            => $initials,
                'cnic'                => $emp->cnic ?? '-',
                'nationality'         => $emp->nationality ?? '-',
                'gender'              => $emp->gender ?? '-',
                'organization'        => $emp->organization?->name ?? '-',
                'sbu'                 => $sbuLabel,
                'department'          => $departmentLabel,
                'role'                => $emp->role?->name ?? '-',
                'join_date'           => $emp->join_date?->format('d M Y') ?? '-',
                'designation'         => $emp->designation ?? '-',
                'verification_status' => $emp->policeVerification?->verification_status ?? '-',
                'email'               => $emp->contact?->email ?? $emp->email ?? '-',
                'cell_no'             => $emp->contact?->cell_no ?? '-',
                'employment_type'     => $emp->employment_type ?? '-',
                'employee_type'       => $employeeType,
                'biometric_id'        => $biometricId,
                'sync_status'         => $syncStatus,
                'site'                => $emp->site ?? '-',
                'floor_access'        => (bool) $emp->floor_access,
                'is_active'           => (bool) $emp->is_active,
            ];
        })->values()->all();
    }

    public function getStats(): array
    {
        $base = Employee::query();

        $total = (clone $base)->count();
        $active = (clone $base)->where('is_active', true)->count();

        $biometricLinked = (clone $base)->whereNotNull('biometric_id')->count();
        $synced = (clone $base)->whereNotNull('biometric_id')->where('sync_with_biometric', true)->count();
        $pending = $biometricLinked - $synced;

        $outsourced = (clone $base)->where('employment_type', 'Third-party')->count();
        $internal = (clone $base)->where(function ($q) {
            $q->whereNull('employment_type')->orWhere('employment_type', '!=', 'Third-party');
        })->count();
        $permanent = (clone $base)->where('employment_type', 'Permanent')->count();
        $contract = (clone $base)->where('employment_type', 'Contract')->count();

        return [
            'total'            => $total,
            'active'           => $active,
            'biometric_linked' => $biometricLinked,
            'pending_sync'     => $pending,
            'internal'         => $internal,
            'permanent'        => $permanent,
            'contract'         => $contract,
            'outsourced'       => $outsourced,
            'vendors'          => 0,
            'synced'           => $synced,
            'pending'          => $pending,
            'failed'           => 0,
        ];
    }

    private function getInitials(string $name): string
    {
        $words = array_values(array_filter(explode(' ', trim($name))));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        }
        return strtoupper(substr($name, 0, 2)) ?: '??';
    }

    public function edit(int $id): View
    {
        $employee = Employee::with([
            'policeVerification',
            'armedForce',
            'contact',
            'bankDetails',
            'familyMembers',
            'academics',
            'exEmployments',
            'medical',
            'references',
            'mediaFiles',
            'sbu:id,name,organization_id',
            'role:id,name',
        ])->findOrFail($id);

        $formData = $this->getFormData();

        $photo     = $employee->mediaFiles->where('file_type', 'photo')->first();
        $photoUrl  = $photo ? Storage::url($photo->file_path) : null;
        $attachments = $employee->mediaFiles
            ->where('file_type', 'attachment')
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->title ?: $m->file_name,
                'type' => $m->attachment_type,
                'description' => $m->description,
                'file_name' => $m->file_name,
                'mime_type' => $m->mime_type,
                'url' => Storage::url($m->file_path),
            ])
            ->values()
            ->all();

        $police     = $employee->policeVerification;
        $armedForce = $employee->armedForce;
        $contact    = $employee->contact;
        $bankRows = $employee->bankDetails;
        $medical    = $employee->medical;

        $rawDeptIds = $employee->department_ids;
        if (! is_array($rawDeptIds)) {
            $rawDeptIds = $rawDeptIds !== null && $rawDeptIds !== '' ? [$rawDeptIds] : [];
        }
        if ($employee->department_id) {
            $rawDeptIds[] = $employee->department_id;
        }
        $deptIdsForLabels = array_values(array_unique(array_filter(array_map('intval', $rawDeptIds))));
        $savedDepartments = $deptIdsForLabels === [] ? [] : Department::query()
            ->whereIn('id', $deptIdsForLabels)
            ->orderByDesc('id')
            ->get(['id', 'name', 'sbu_id'])
            ->map(fn ($row) => ['id' => $row->id, 'name' => $row->name, 'sbu_id' => $row->sbu_id])
            ->values()
            ->all();

        $resolvedSbuId = $employee->sbu_id;
        if (! $resolvedSbuId && ! empty($savedDepartments)) {
            $resolvedSbuId = $savedDepartments[0]['sbu_id'] ?? null;
        }
        $resolvedSbuName = $employee->sbu?->name;
        if (! $resolvedSbuName && $resolvedSbuId) {
            $resolvedSbuName = Sbu::query()->whereKey((int) $resolvedSbuId)->value('name');
        }

        $editData = [
            'id'                  => $employee->id,
            'organization_id'     => $employee->organization_id,
            'sbu_id'              => $resolvedSbuId,
            'sbu_name'            => $resolvedSbuName,
            'department_id'       => $employee->department_id,
            'role_id'             => $employee->role_id,
            'role_name'           => $employee->role?->name,
            'saved_departments'   => $savedDepartments,
            'employee_code'       => $employee->employee_code,
            'full_name'           => $employee->full_name,
            'father_name'         => $employee->father_name,
            'cnic'                => $employee->cnic,
            'cnic_expiry'         => $employee->cnic_expiry?->format('Y-m-d'),
            'father_cnic'         => $employee->father_cnic,
            'nationality'         => $employee->nationality,
            'dob'                 => $employee->dob?->format('Y-m-d'),
            'ntn'                 => $employee->ntn,
            'gender'              => $employee->gender,
            'domicile_district'   => $employee->domicile_district,
            'domicile_province'   => $employee->domicile_province,
            'city_of_birth'       => $employee->city_of_birth,
            'religion'            => $employee->religion,
            'sect'                => $employee->sect,
            'spouse_name'         => $employee->spouse_name,
            'spouse_cnic'         => $employee->spouse_cnic,
            'spouse_nationality'  => $employee->spouse_nationality,
            'marital_status'      => $employee->marital_status,
            'department_ids'      => $deptIdsForLabels,
            'nok_name'            => $employee->nok_name,
            'nok_cnic'            => $employee->nok_cnic,
            'nok_relation'        => $employee->nok_relation,
            'nok_dob'             => $employee->nok_dob instanceof \Carbon\Carbon ? $employee->nok_dob->format('Y-m-d') : null,
            'nok_contact'         => $employee->nok_contact,
            'is_ex_armed_force'   => (bool) $employee->is_ex_armed_force || $armedForce !== null,
            'join_date'           => $employee->join_date instanceof \Carbon\Carbon ? $employee->join_date->format('Y-m-d') : null,
            'designation'         => $employee->designation,
            'grade'               => $employee->grade,
            'branch'              => $employee->branch,
            'location'            => $employee->location,
            'biometric_id'        => $employee->biometric_id,
            'employment_category' => $employee->employment_category,
            'intern_type'         => $employee->intern_type,
            'intern_duration'     => $employee->intern_duration,
            'contractual_type'    => $employee->contractual_type,
            'employment_type'     => $employee->employment_type,
            'contract_start_date' => $employee->contract_start_date instanceof \Carbon\Carbon ? $employee->contract_start_date->format('Y-m-d') : null,
            'contract_end_date'   => $employee->contract_end_date instanceof \Carbon\Carbon ? $employee->contract_end_date->format('Y-m-d') : null,
            'engagement_mode'     => $employee->engagement_mode,
            'hybrid_days'         => is_array($employee->hybrid_days) ? array_values($employee->hybrid_days) : $employee->hybrid_days,
            'standard_schedule_mode' => $employee->standard_schedule_mode,
            'working_days'        => is_array($employee->working_days) ? array_values($employee->working_days) : $employee->working_days,
            'working_start_time'  => $employee->working_start_time
                ? (is_string($employee->working_start_time) ? substr($employee->working_start_time, 0, 5) : $employee->working_start_time->format('H:i'))
                : null,
            'working_end_time'    => $employee->working_end_time
                ? (is_string($employee->working_end_time) ? substr($employee->working_end_time, 0, 5) : $employee->working_end_time->format('H:i'))
                : null,
            'opening_grace_period' => $employee->opening_grace_period,
            'closing_grace_period' => $employee->closing_grace_period,
            'photo_url'           => $photoUrl,
            'attachments'         => $attachments,
            'police' => $police ? [
                'verification_status'    => $police->verification_status,
                'msr_letter_no'          => $police->msr_letter_no,
                'addressee'              => $police->addressee,
                'verifying_authority'    => $police->verifying_authority,
                'verification_letter_no' => $police->verification_letter_no,
                'next_verification_date' => $police->next_verification_date?->format('Y-m-d'),
                'remarks'                => $police->remarks,
            ] : null,
            'armed_force' => $armedForce ? [
                'service_no'            => $armedForce->service_no,
                'rank'                  => $armedForce->rank,
                'medical_category'      => $armedForce->medical_category,
                'date_of_commissioning' => $armedForce->date_of_commissioning?->format('Y-m-d'),
                'date_of_retirement'    => $armedForce->date_of_retirement?->format('Y-m-d'),
                'reason_of_retirement'  => $armedForce->reason_of_retirement,
                'corps_regiment'        => $armedForce->corps_regiment,
                'ex_army_unit'          => $armedForce->ex_army_unit,
                'trade'                 => $armedForce->trade,
                'pma_lc_ots'            => $armedForce->pma_lc_ots,
            ] : null,
            'contact' => $contact ? [
                'residence_phone'   => $contact->residence_phone,
                'emergency_contact' => $contact->emergency_contact,
                'cell_no'           => $contact->cell_no,
                'email'             => $contact->email,
                'present_address'   => $contact->present_address,
                'permanent_address' => $contact->permanent_address,
            ] : null,
            'bank_details' => $bankRows->map(fn ($b) => [
                'id'                 => $b->id,
                'account_category'   => $b->account_category,
                'account_title'      => $b->account_title,
                'account_no'         => $b->account_no,
                'bank_name'          => $b->bank_name,
                'branch_code'        => $b->branch_code,
                'branch_address'     => $b->branch_address ?: $b->bank_branch,
                'iban'               => $b->iban,
                'account_type'       => $b->account_type,
                'is_salary_account'  => (bool) $b->is_salary_account,
            ])->values()->all(),
            'family' => $employee->familyMembers->map(fn($m) => [
                'id'         => $m->id,
                'name'       => $m->name,
                'gender'     => $m->gender,
                'dob'        => $m->dob?->format('Y-m-d'),
                'relation'   => $m->relation,
                'occupation' => $m->occupation,
            ])->values()->all(),
            'academics' => $employee->academics->map(fn($a) => [
                'id'             => $a->id,
                'degree'         => $a->degree,
                'grade_cgpa'     => $a->grade_cgpa,
                'start_date'     => $a->start_date?->format('Y-m-d'),
                'end_date'       => $a->end_date?->format('Y-m-d'),
                'field_of_study' => $a->field_of_study,
                'institute'      => $a->institute,
            ])->values()->all(),
            'employments' => $employee->exEmployments->map(fn($e) => [
                'id'                 => $e->id,
                'organization'       => $e->organization,
                'designation'        => $e->designation,
                'from_date'          => $e->from_date?->format('Y-m-d'),
                'to_date'            => $e->to_date?->format('Y-m-d'),
                'salary'             => $e->salary,
                'reason_for_leaving' => $e->reason_for_leaving,
            ])->values()->all(),
            'medical' => $medical ? [
                'last_fitness_test'      => $medical->last_fitness_test,
                'has_disability'         => $medical->has_disability,
                'blood_group'            => $medical->blood_group,
                'disability_type'        => $medical->disability_type,
                'disability_description' => $medical->disability_description,
            ] : null,
            'references' => $employee->references->map(fn($r) => [
                'ref_number'   => $r->ref_number,
                'name'         => $r->name,
                'designation'  => $r->designation,
                'organization' => $r->organization,
                'contact_no'   => $r->contact_no,
                'relationship' => $r->relationship,
            ])->values()->all(),
        ];

        return view('admin.employeeregisteration.index', array_merge($formData, [
            'employee' => $employee,
            'editData' => $editData,
        ]));
    }

    public function update(int $id, array $data, array $files = [], array $attachments = [], array $keptAttachmentIds = []): Employee
    {
        return DB::transaction(function () use ($id, $data, $files, $attachments, $keptAttachmentIds) {
            $employee = Employee::findOrFail($id);
            $role      = Role::find($data['role_id'] ?? $employee->role_id);
            $orgLevel  = $role && $role->isOrganizationLevelRole();

            $code = $employee->employee_code;
            if (!$code && $role) {
                if ($orgLevel) {
                    $sbuForCode = Sbu::where('organization_id', (int) ($data['organization_id'] ?? $employee->organization_id))->orderBy('id')->value('id');
                    if ($sbuForCode) {
                        $code = $this->generateNextCode((int) $sbuForCode);
                    }
                } else {
                    $sbuId = isset($data['sbu_id']) ? (int) $data['sbu_id'] : $employee->sbu_id;
                    if ($sbuId) {
                        $code = $this->generateNextCode((int) $sbuId);
                    }
                }
            }

            $otherStepColumnNames = [
                'full_name', 'father_name', 'employee_type', 'employment_type', 'designation', 'grade',
                'branch', 'location', 'phone', 'cnic', 'cnic_expiry', 'father_cnic', 'ntn', 'gender',
                'nationality', 'dob', 'domicile_district', 'domicile_province', 'city_of_birth', 'religion',
                'sect', 'marital_status', 'spouse_name', 'nok_name', 'nok_cnic', 'nok_relation', 'nok_dob',
                'nok_contact', 'site', 'join_date', 'floor_access', 'biometric_id', 'sync_with_biometric',
                'verification_status', 'msr_letter_no', 'addressee', 'verifying_authority', 'verification_letter_no',
                'next_verification_date', 'police_remarks', 'service_no', 'rank', 'medical_category',
                'date_of_commissioning', 'date_of_retirement', 'reason_of_retirement', 'corps_regiment',
                'ex_army_unit', 'trade', 'pma_lc_ots', 'residence_phone', 'emergency_contact', 'cell_no',
                'present_address', 'permanent_address', 'last_fitness_test', 'has_disability', 'blood_group',
                'disability_type', 'disability_description', 'ref1_name', 'ref1_designation', 'ref1_organization',
                'ref1_contact', 'ref1_relationship', 'ref2_name', 'ref2_designation', 'ref2_organization',
                'ref2_contact', 'ref2_relationship', 'employment_category', 'intern_type', 'intern_duration',
                'contractual_type', 'contract_start_date', 'contract_end_date', 'engagement_mode', 'hybrid_days',
                'standard_schedule_mode', 'working_days', 'working_start_time', 'working_end_time', 'opening_grace_period', 'closing_grace_period',
                'spouse_cnic', 'spouse_nationality', 'nok_cnic_expiry_date',
                'organization_id', 'role_id', 'sbu_id', 'department_id', 'department_ids',
                'is_ex_armed_force',
                'is_father_deceased',
            ];

            $step = (int) ($data['step'] ?? 0);

            if ($step === 1) {
                $updatePayload = $this->generalInformation->buildUpdatePayload($data);
            } elseif ($step === 2) {
                $updatePayload = $this->employmentInformation->buildUpdatePayload($data, $orgLevel);
            } elseif ($step === 0) {
                $updatePayload = array_merge(
                    $this->generalInformation->buildUpdatePayload($data),
                    $this->employmentInformation->buildUpdatePayload($data, $orgLevel)
                );
                foreach ($otherStepColumnNames as $field) {
                    if (! array_key_exists($field, $updatePayload) && array_key_exists($field, $data)) {
                        $updatePayload[$field] = $data[$field] === '' ? null : $data[$field];
                    }
                }
            } else {
                $updatePayload = [];
                foreach ($otherStepColumnNames as $field) {
                    if (array_key_exists($field, $data)) {
                        $updatePayload[$field] = $data[$field] === '' ? null : $data[$field];
                    }
                }
                if (array_key_exists('organization_id', $data)) {
                    $updatePayload['organization_id'] = $data['organization_id'];
                }
                if (array_key_exists('role_id', $data)) {
                    $updatePayload['role_id'] = $data['role_id'];
                }
                if (array_key_exists('sbu_id', $data)) {
                    $updatePayload['sbu_id'] = $data['sbu_id'];
                }
                if (array_key_exists('department_id', $data)) {
                    $updatePayload['department_id'] = $data['department_id'];
                }
                if (array_key_exists('department_ids', $data)) {
                    $updatePayload['department_ids'] = $data['department_ids'];
                }
            }

            if (array_key_exists('email', $data) || array_key_exists('contact_email', $data)) {
                $updatePayload['email'] = $data['email'] ?? $data['contact_email'] ?? $employee->email;
            }

            $updatePayload['employee_code'] = $code;

            $employee->update($updatePayload);

            if (($step === 1 || $step === 0) && array_key_exists('is_ex_armed_force', $updatePayload) && ! $updatePayload['is_ex_armed_force']) {
                $employee->armedForce()->delete();
            }

            // Sync with associated user account if it exists
            if ($employee->user) {
                $userUpdateData = [];
                $emailToSync = $data['email'] ?? $data['contact_email'] ?? null;

                if ($emailToSync) {
                    $userUpdateData['email'] = $emailToSync;
                }

                if (!empty($data['full_name'])) {
                    $userUpdateData['name'] = $data['full_name'];
                }

                if (!empty($userUpdateData)) {
                    $employee->user->update($userUpdateData);
                    Log::info('Associated user account synced', ['user_id' => $employee->user->id, 'updates' => array_keys($userUpdateData), 'email' => $emailToSync ?? 'no change']);
                }
            }

            // Step 3 - Police Verification
            if ($step === 3 || $step === 0) {
                $employee->policeVerification()->delete();
                $this->savePoliceVerification($employee->id, $data);
            }

            if ($step === 4 || $step === 0) {
                if ($step === 4) {
                    $employee->armedForce()->delete();
                    $this->saveArmedForce($employee->id, $data);
                } elseif ($employee->is_ex_armed_force) {
                    $employee->armedForce()->delete();
                    $this->saveArmedForce($employee->id, $data);
                } else {
                    $employee->armedForce()->delete();
                }
            }

            if ($step === 5 || $step === 0) {
                $employee->bankDetails()->delete();
                $this->saveBankDetails($employee->id, $data['banks'] ?? []);
            }

            // Step 6 - More (Family, Academics, Employment History, Medical, References, Contact)
            if ($step === 6 || $step === 0) {
                $employee->contact()->delete();
                $this->saveContact($employee->id, $data);

                if (isset($data['family'])) {
                    $employee->familyMembers()->delete();
                    $this->saveFamilyMembers($employee->id, $data['family']);
                }

                if (isset($data['academics'])) {
                    $employee->academics()->delete();
                    $this->saveAcademics($employee->id, $data['academics']);
                }
                
                if (isset($data['employments'])) {
                    $employee->exEmployments()->delete();
                    $this->saveExEmployments($employee->id, $data['employments']);
                }

                $employee->medical()->delete();
                $this->saveMedical($employee->id, $data);

                $employee->references()->delete();
                $this->saveReferences($employee->id, $data);
            }

            if (!empty($files)) {
                $employee->mediaFiles()->where('file_type', 'photo')->delete();
                $this->saveMediaFiles($employee->id, $files);
            }

            $employee->mediaFiles()
                ->where('file_type', 'attachment')
                ->when(!empty($keptAttachmentIds), fn($q) => $q->whereNotIn('id', $keptAttachmentIds))
                ->when(empty($keptAttachmentIds), fn($q) => $q)
                ->delete();

            $this->saveAttachmentFiles($employee->id, $attachments);

            Log::info('Employee updated', ['id' => $employee->id]);

            return $employee;
        });
    }

    public function destroy(int $id): void
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        Log::info('Employee deleted', ['id' => $id]);
    }
}
