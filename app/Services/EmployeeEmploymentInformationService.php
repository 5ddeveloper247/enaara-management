<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Role;
use App\Models\Sbu;

class EmployeeEmploymentInformationService
{
    public function employeeAttributeNames(): array
    {
        return [
            'employment_category',
            'intern_type',
            'intern_duration',
            'contractual_type',
            'contract_start_date',
            'contract_end_date',
            'engagement_mode',
            'hybrid_days',
            'organization_id',
            'role_id',
            'sbu_id',
            'department_id',
            'department_ids',
            'join_date',
            'designation',
            'grade',
            'branch',
            'location',
            'biometric_id',
            'employee_type',
            'employment_type',
            'site',
            'employee_status',
            'termination_reason',
            'termination_date',
            'suspension_reason',
            'suspension_start_date',
            'suspension_end_date',
            'probation_start_date',
            'probation_end_date',
        ];
    }

    public function buildUpdatePayload(array $data, bool $orgLevel): array
    {
        $payload = [];
        foreach ($this->employeeAttributeNames() as $field) {
            if ($field === 'contract_start_date') {
                $val = ! empty($data['contract_start_date'])
                    ? $data['contract_start_date']
                    : (! empty($data['employee_contract_start_date'])
                        ? $data['employee_contract_start_date']
                        : (! empty($data['probation_contract_start_date']) ? $data['probation_contract_start_date'] : null));
                $payload['contract_start_date'] = $val === '' || $val === null ? null : $val;
                continue;
            }
            if ($field === 'contract_end_date') {
                $val = ! empty($data['contract_end_date'])
                    ? $data['contract_end_date']
                    : (! empty($data['employee_contract_end_date']) ? $data['employee_contract_end_date'] : null);
                $payload['contract_end_date'] = $val === '' || $val === null ? null : $val;
                continue;
            }
            if (! array_key_exists($field, $data)) {
                continue;
            }
            $payload[$field] = $data[$field] === '' ? null : $data[$field];
        }

        $mode = $payload['engagement_mode'] ?? $data['engagement_mode'] ?? null;
        if ($mode !== 'hybrid') {
            $payload['hybrid_days'] = null;
        }

        $cat = $payload['employment_category'] ?? $data['employment_category'] ?? null;
        if ($cat === 'intern') {
            $payload['employment_type'] = null;
            $payload['contractual_type'] = null;
            $payload['contract_start_date'] = null;
            $payload['contract_end_date'] = null;
        } elseif ($cat === 'consultant') {
            $payload['intern_type'] = null;
            $payload['intern_duration'] = null;
            $payload['employment_type'] = null;
            $payload['contractual_type'] = null;
        } elseif ($cat === 'employee') {
            $payload['intern_type'] = null;
            $payload['intern_duration'] = null;
            $et = $payload['employment_type'] ?? $data['employment_type'] ?? null;
            if ($et !== 'contractual') {
                $payload['contractual_type'] = null;
                $payload['contract_start_date'] = null;
                $payload['contract_end_date'] = null;
            } else {
                $ct = $payload['contractual_type'] ?? $data['contractual_type'] ?? null;
                if ($ct !== 'time_bound') {
                    $payload['contract_start_date'] = null;
                    $payload['contract_end_date'] = null;
                }
            }
        }

        $roleId = isset($payload['role_id']) ? (int) $payload['role_id'] : (int) ($data['role_id'] ?? 0);
        $role = $roleId > 0 ? Role::query()->find($roleId) : null;
        $merged = array_merge($payload, $this->standardScheduleAttributesForPersist($data, $role, $orgLevel));

        $status = $merged['employee_status'] ?? $data['employee_status'] ?? null;
        if ($status !== 'Terminated') {
            $merged['termination_reason'] = null;
            $merged['termination_date'] = null;
        }

        if ($status !== 'Suspend') {
            $merged['suspension_reason'] = null;
            $merged['suspension_start_date'] = null;
            $merged['suspension_end_date'] = null;
        }

        return $merged;
    }

    public function standardScheduleAttributesForPersist(array $data, ?Role $role, bool $orgLevel): array
    {
        $engagement = $data['engagement_mode'] ?? null;
        if ($engagement !== 'standard') {
            return $this->blankStandardSchedulePayload();
        }

        $schedMode = $data['standard_schedule_mode'] ?? 'default';
        if (! in_array($schedMode, ['default', 'custom'], true)) {
            $schedMode = 'default';
        }

        if ($schedMode === 'custom') {
            $days = $this->normalizeWorkingDaysInput($data['working_days'] ?? null);
            $grace = $this->syncedGracePeriodFromPayload($data);

            return [
                'standard_schedule_mode' => 'custom',
                'working_days' => $days,
                'working_start_time' => $this->normalizeTimeForStore($data['working_start_time'] ?? null),
                'working_end_time' => $this->normalizeTimeForStore($data['working_end_time'] ?? null),
                'opening_grace_period' => $grace,
                'closing_grace_period' => $grace,
            ];
        }

        $orgId = (int) ($data['organization_id'] ?? 0);
        $sbuId = isset($data['sbu_id']) && $data['sbu_id'] !== '' && $data['sbu_id'] !== null ? (int) $data['sbu_id'] : null;
        $resolved = $this->resolveStandardScheduleFromRole($role, $orgLevel, $orgId, $sbuId);

        return array_merge(['standard_schedule_mode' => 'default'], $resolved);
    }

    protected function blankStandardSchedulePayload(): array
    {
        return [
            'standard_schedule_mode' => null,
            'working_days' => null,
            'working_start_time' => null,
            'working_end_time' => null,
            'opening_grace_period' => null,
            'closing_grace_period' => null,
        ];
    }

    protected function resolveStandardScheduleFromRole(?Role $role, bool $orgLevel, int $organizationId, ?int $sbuId): array
    {
        if ($orgLevel && $organizationId > 0) {
            return $this->scheduleShapeFromModel(Organization::query()->find($organizationId));
        }
        if ($role && $role->department_id) {
            return $this->scheduleShapeFromModel(Department::query()->find((int) $role->department_id));
        }
        if ($sbuId) {
            return $this->scheduleShapeFromModel(Sbu::query()->find($sbuId));
        }

        return $this->scheduleShapeFromModel(null);
    }

    protected function scheduleShapeFromModel(?object $model): array
    {
        if (! $model) {
            return [
                'working_days' => null,
                'working_start_time' => null,
                'working_end_time' => null,
                'opening_grace_period' => null,
                'closing_grace_period' => null,
            ];
        }

        $days = $model->working_days ?? null;
        if (is_array($days)) {
            $days = array_values(array_filter($days, static fn ($d) => $d !== null && $d !== ''));
            if ($days === []) {
                $days = null;
            }
        } else {
            $days = null;
        }

        return [
            'working_days' => $days,
            'working_start_time' => $this->normalizeTimeForStore($model->working_start_time ?? null),
            'working_end_time' => $this->normalizeTimeForStore($model->working_end_time ?? null),
            'opening_grace_period' => $this->normalizeGraceInput($model->opening_grace_period ?? null),
            'closing_grace_period' => $this->normalizeGraceInput($model->closing_grace_period ?? null),
        ];
    }

    protected function normalizeWorkingDaysInput(mixed $raw): ?array
    {
        if (! is_array($raw)) {
            return null;
        }
        $allowed = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $out = [];
        foreach ($raw as $d) {
            $v = is_string($d) ? strtolower(trim($d)) : '';
            if (in_array($v, $allowed, true)) {
                $out[] = $v;
            }
        }
        $out = array_values(array_unique($out));

        return $out === [] ? null : $out;
    }

    protected function normalizeTimeForStore(mixed $t): ?string
    {
        if ($t === null || $t === '') {
            return null;
        }
        if ($t instanceof \DateTimeInterface) {
            return $t->format('H:i');
        }
        $s = (string) $t;

        return strlen($s) >= 5 ? substr($s, 0, 5) : $s;
    }

    protected function normalizeGraceInput(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }

        return (int) $v;
    }

    protected function syncedGracePeriodFromPayload(array $data): ?int
    {
        if (array_key_exists('grace_period', $data)) {
            return $this->normalizeGraceInput($data['grace_period'] ?? null);
        }
        $open = $this->normalizeGraceInput($data['opening_grace_period'] ?? null);
        if ($open !== null) {
            return $open;
        }

        return $this->normalizeGraceInput($data['closing_grace_period'] ?? null);
    }
}
