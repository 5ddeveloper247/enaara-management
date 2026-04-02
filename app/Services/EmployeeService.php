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
use App\Models\Organization;
use App\Models\Sbu;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    protected $auditTrailService;

    public function __construct(AuditTrailService $auditTrailService)
    {
        $this->auditTrailService = $auditTrailService;
    }

    public function index(): View
    {
        $organizations = Organization::with('sbus.departments')->orderBy('name')->get();
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'organization_id', 'department_id']);
        $employees     = Employee::with(['organization', 'department'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.employee.index', compact('organizations', 'employees'));
    }

    public function getFormData(): array
    {
        $organizations = Organization::with('sbus.departments')->orderBy('name')->get();
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'organization_id', 'department_id']);

        $orgsData = $organizations->map(fn($o) => [
            'id'   => $o->id,
            'name' => $o->name,
            'sbus' => $o->sbus->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'departments' => $s->departments->map(fn($d) => [
                    'id'   => $d->id,
                    'name' => $d->name,
                ])->values()->all(),
            ])->values()->all(),
        ])->values()->all();

        $rolesData = $roles->map(fn($r) => [
            'id'              => $r->id,
            'name'            => $r->name,
            'organization_id' => $r->organization_id,
            'department_id'   => $r->department_id,
        ])->values()->all();

        return compact('organizations', 'orgsData', 'rolesData');
    }

    public function store(array $data, array $files = [], array $attachments = []): Employee
    {
        return DB::transaction(function () use ($data, $files, $attachments) {
            $sbuId = isset($data['sbu_id']) ? (int) $data['sbu_id'] : null;
            if (!$sbuId) {
                throw new \InvalidArgumentException('SBU is required to generate employee code.');
            }

            $code = $this->generateNextCode($sbuId);

            $employee = Employee::create([
                'full_name'           => $data['full_name'],
                'father_name'         => $data['father_name'] ?? null,
                'employee_code'       => $code,
                'organization_id'     => $data['organization_id'] ?? null,
                'sbu_id'              => $data['sbu_id'] ?? null,
                'department_id'       => $data['department_id'] ?? null,
                'role_id'             => $data['role_id'] ?? null,
                'employee_type'       => $data['employee_type'] ?? null,
                'employment_type'     => $data['employment_type'] ?? null,
                'designation'         => $data['designation'] ?? null,
                'grade'               => $data['grade'] ?? null,
                'branch'              => $data['branch'] ?? null,
                'location'            => $data['location'] ?? null,
                'email'               => !empty($data['email']) ? $data['email'] : null,
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
                'nok_name'            => $data['nok_name'] ?? null,
                'nok_cnic'            => $data['nok_cnic'] ?? null,
                'nok_relation'        => $data['nok_relation'] ?? null,
                'nok_dob'             => !empty($data['nok_dob']) ? $data['nok_dob'] : null,
                'nok_contact'         => $data['nok_contact'] ?? null,
                'site'                => $data['site'] ?? null,
                'join_date'           => !empty($data['join_date']) ? $data['join_date'] : null,
                'floor_access'        => isset($data['floor_access']) ? (bool) $data['floor_access'] : false,
                'biometric_id'        => $data['biometric_id'] ?? null,
                'employment_category' => $data['employment_category'] ?? null,
                'intern_type'         => $data['intern_type'] ?? null,
                'intern_duration'     => $data['intern_duration'] ?? null,
                'contractual_type'    => $data['contractual_type'] ?? null,
                'engagement_mode'     => $data['engagement_mode'] ?? null,
                'hybrid_days'         => $data['hybrid_days'] ?? null,
                'sync_with_biometric' => isset($data['sync_with_biometric']) ? (bool) $data['sync_with_biometric'] : false,
                'is_active'           => true,
            ]);

            $this->savePoliceVerification($employee->id, $data);
            $this->saveArmedForce($employee->id, $data);
            $this->saveContact($employee->id, $data);
            $this->saveBankDetail($employee->id, $data);
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

    private function savePoliceVerification(int $id, array $d): void
    {
        if (empty($d['verification_status']) && empty($d['msr_letter_no'])) return;
        EmployeePoliceVerification::create([
            'employee_id'            => $id,
            'verification_status'    => $d['verification_status'] ?? null,
            'msr_letter_no'          => $d['msr_letter_no'] ?? null,
            'addressee'              => $d['addressee'] ?? null,
            'verifying_authority'    => $d['verifying_authority'] ?? null,
            'verification_letter_no' => $d['verification_letter_no'] ?? null,
            'next_verification_date' => !empty($d['next_verification_date']) ? $d['next_verification_date'] : null,
            'remarks'                => $d['police_remarks'] ?? null,
        ]);
    }

    private function saveArmedForce(int $id, array $d): void
    {
        if (empty($d['service_no']) && empty($d['rank'])) return;
        EmployeeArmedForce::create([
            'employee_id'           => $id,
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
        ]);
    }

    private function saveContact(int $id, array $d): void
    {
        if (empty($d['residence_phone']) && empty($d['cell_no']) && empty($d['present_address'])) return;
        EmployeeContact::create([
            'employee_id'       => $id,
            'residence_phone'   => $d['residence_phone'] ?? null,
            'emergency_contact' => $d['emergency_contact'] ?? null,
            'cell_no'           => $d['cell_no'] ?? null,
            'email'             => $d['contact_email'] ?? null,
            'present_address'   => $d['present_address'] ?? null,
            'permanent_address' => $d['permanent_address'] ?? null,
        ]);
    }

    private function saveBankDetail(int $id, array $d): void
    {
        if (empty($d['account_title']) && empty($d['account_no'])) return;
        EmployeeBankDetail::create([
            'employee_id'   => $id,
            'account_title' => $d['account_title'] ?? null,
            'account_no'    => $d['account_no'] ?? null,
            'bank_branch'   => $d['bank_branch'] ?? null,
            'account_type'  => $d['account_type'] ?? null,
        ]);
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

    private function saveMedical(int $id, array $d): void
    {
        if (empty($d['last_fitness_test']) && empty($d['blood_group'])) return;
        EmployeeMedical::create([
            'employee_id'            => $id,
            'last_fitness_test'      => $d['last_fitness_test'] ?? null,
            'has_disability'         => $d['has_disability'] ?? null,
            'blood_group'            => $d['blood_group'] ?? null,
            'disability_type'        => $d['disability_type'] ?? null,
            'disability_description' => $d['disability_description'] ?? null,
        ]);
    }

    private function saveReferences(int $id, array $d): void
    {
        for ($i = 1; $i <= 2; $i++) {
            if (empty($d["ref{$i}_name"])) continue;
            EmployeeReference::create([
                'employee_id'  => $id,
                'ref_number'   => $i,
                'name'         => $d["ref{$i}_name"],
                'designation'  => $d["ref{$i}_designation"] ?? null,
                'organization' => $d["ref{$i}_organization"] ?? null,
                'contact_no'   => $d["ref{$i}_contact"] ?? null,
                'relationship' => $d["ref{$i}_relationship"] ?? null,
            ]);
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

    public function getTableData(): array
    {
        $employees = Employee::with(['department', 'organization', 'mediaFiles'])
            ->orderByDesc('id')
            ->get();

        return $employees->map(function (Employee $emp) {
            $initials    = $this->getInitials($emp->full_name ?? '');
            $biometricId = $emp->biometric_id;
            $syncStatus  = $biometricId
                ? ($emp->sync_with_biometric ? 'Synced' : 'Pending')
                : 'Not Linked';
            $employeeType = ($emp->employment_type === 'Third-party') ? 'Third-party' : 'Internal';

            $photo     = $emp->mediaFiles->where('file_type', 'photo')->first();
            $photoUrl  = $photo ? Storage::url($photo->file_path) : null;

            return [
                'id'              => $emp->id,
                'employee_code'   => $emp->employee_code ?? '-',
                'full_name'       => $emp->full_name ?? '-',
                'initials'        => $initials,
                'photo_url'       => $photoUrl,
                'department'      => $emp->department?->name ?? '-',
                'organization'    => $emp->organization?->name ?? '-',
                'employment_type' => $emp->employment_type ?? '-',
                'employee_type'   => $employeeType,
                'biometric_id'    => $biometricId,
                'sync_status'     => $syncStatus,
                'site'            => $emp->site ?? '-',
                'floor_access'    => (bool) $emp->floor_access,
                'is_active'       => (bool) $emp->is_active,
            ];
        })->values()->all();
    }

    public function getStats(): array
    {
        $employees = Employee::whereNull('deleted_at')
            ->get(['employment_type', 'biometric_id', 'sync_with_biometric', 'is_active']);

        $total          = $employees->count();
        $active         = $employees->where('is_active', true)->count();
        $hasbiometric   = $employees->whereNotNull('biometric_id');
        $biometricLinked= $hasbiometric->count();
        $synced         = $hasbiometric->where('sync_with_biometric', true)->count();
        $pending        = $hasbiometric->where('sync_with_biometric', false)->count();
        $internal       = $employees->where('employment_type', '!=', 'Third-party')->count();
        $permanent      = $employees->where('employment_type', 'Permanent')->count();
        $contract       = $employees->where('employment_type', 'Contract')->count();
        $outsourced     = $employees->where('employment_type', 'Third-party')->count();

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
            'policeVerification', 'armedForce', 'contact', 'bankDetail',
            'familyMembers', 'academics', 'exEmployments', 'medical',
            'references', 'mediaFiles',
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
        $bank       = $employee->bankDetail;
        $medical    = $employee->medical;

        $editData = [
            'id'                  => $employee->id,
            'organization_id'     => $employee->organization_id,
            'sbu_id'              => $employee->sbu_id,
            'department_id'       => $employee->department_id,
            'role_id'             => $employee->role_id,
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
            'marital_status'      => $employee->marital_status,
            'nok_name'            => $employee->nok_name,
            'nok_cnic'            => $employee->nok_cnic,
            'nok_relation'        => $employee->nok_relation,
            'nok_dob'             => $employee->nok_dob instanceof \Carbon\Carbon ? $employee->nok_dob->format('Y-m-d') : null,
            'nok_contact'         => $employee->nok_contact,
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
            'engagement_mode'     => $employee->engagement_mode,
            'hybrid_days'         => $employee->hybrid_days,
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
            'bank' => $bank ? [
                'account_title' => $bank->account_title,
                'account_no'    => $bank->account_no,
                'bank_branch'   => $bank->bank_branch,
                'account_type'  => $bank->account_type,
            ] : null,
            'family' => $employee->familyMembers->map(fn($m) => [
                'name'       => $m->name,
                'gender'     => $m->gender,
                'dob'        => $m->dob?->format('Y-m-d'),
                'relation'   => $m->relation,
                'occupation' => $m->occupation,
            ])->values()->all(),
            'academics' => $employee->academics->map(fn($a) => [
                'degree'         => $a->degree,
                'grade_cgpa'     => $a->grade_cgpa,
                'start_date'     => $a->start_date?->format('Y-m-d'),
                'end_date'       => $a->end_date?->format('Y-m-d'),
                'field_of_study' => $a->field_of_study,
                'institute'      => $a->institute,
            ])->values()->all(),
            'employments' => $employee->exEmployments->map(fn($e) => [
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

        return view('admin.register.index', array_merge($formData, [
            'employee' => $employee,
            'editData' => $editData,
        ]));
    }

    public function update(int $id, array $data, array $files = [], array $attachments = [], array $keptAttachmentIds = []): Employee
    {
        return DB::transaction(function () use ($id, $data, $files, $attachments, $keptAttachmentIds) {
            $employee = Employee::findOrFail($id);

            $employee->update([
                'full_name'           => $data['full_name'],
                'father_name'         => $data['father_name'] ?? null,
                'organization_id'     => $data['organization_id'] ?? null,
                'sbu_id'              => $data['sbu_id'] ?? null,
                'department_id'       => $data['department_id'] ?? null,
                'role_id'             => $data['role_id'] ?? null,
                'employee_type'       => $data['employee_type'] ?? null,
                'employment_type'     => $data['employment_type'] ?? null,
                'designation'         => $data['designation'] ?? null,
                'grade'               => $data['grade'] ?? null,
                'branch'              => $data['branch'] ?? null,
                'location'            => $data['location'] ?? null,
                'email'               => !empty($data['email']) ? $data['email'] : null,
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
                'nok_name'            => $data['nok_name'] ?? null,
                'nok_cnic'            => $data['nok_cnic'] ?? null,
                'nok_relation'        => $data['nok_relation'] ?? null,
                'nok_dob'             => !empty($data['nok_dob']) ? $data['nok_dob'] : null,
                'nok_contact'         => $data['nok_contact'] ?? null,
                'site'                => $data['site'] ?? null,
                'join_date'           => !empty($data['join_date']) ? $data['join_date'] : null,
                'floor_access'        => isset($data['floor_access']) ? (bool) $data['floor_access'] : false,
                'biometric_id'        => $data['biometric_id'] ?? null,
                'employment_category' => $data['employment_category'] ?? null,
                'intern_type'         => $data['intern_type'] ?? null,
                'intern_duration'     => $data['intern_duration'] ?? null,
                'contractual_type'    => $data['contractual_type'] ?? null,
                'engagement_mode'     => $data['engagement_mode'] ?? null,
                'hybrid_days'         => $data['hybrid_days'] ?? null,
                'sync_with_biometric' => isset($data['sync_with_biometric']) ? (bool) $data['sync_with_biometric'] : false,
            ]);

            $employee->policeVerification()->delete();
            $employee->armedForce()->delete();
            $employee->contact()->delete();
            $employee->bankDetail()->delete();
            $employee->familyMembers()->delete();
            $employee->academics()->delete();
            $employee->exEmployments()->delete();
            $employee->medical()->delete();
            $employee->references()->delete();

            $this->savePoliceVerification($employee->id, $data);
            $this->saveArmedForce($employee->id, $data);
            $this->saveContact($employee->id, $data);
            $this->saveBankDetail($employee->id, $data);
            $this->saveFamilyMembers($employee->id, $data['family'] ?? []);
            $this->saveAcademics($employee->id, $data['academics'] ?? []);
            $this->saveExEmployments($employee->id, $data['employments'] ?? []);
            $this->saveMedical($employee->id, $data);
            $this->saveReferences($employee->id, $data);

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
