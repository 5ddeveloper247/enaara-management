<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Sbu;
use Carbon\Carbon;

class EmployeeWorkingScheduleService
{
    private const HYBRID_DAY_KEYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    private const HYBRID_SHORT_TO_FULL = [
        'mon' => 'monday',
        'tue' => 'tuesday',
        'wed' => 'wednesday',
        'thu' => 'thursday',
        'fri' => 'friday',
        'sat' => 'saturday',
        'sun' => 'sunday',
    ];

    public function isShiftBased(Employee $employee): bool
    {
        return strtolower(trim((string) ($employee->engagement_mode ?? ''))) === 'shifts';
    }

    public function isHybrid(Employee $employee): bool
    {
        return strtolower(trim((string) ($employee->engagement_mode ?? ''))) === 'hybrid';
    }

    public function resolveWorkingDays(Employee $employee): ?array
    {
        if ($this->isHybrid($employee)) {
            return $this->resolveHybridWorkingDays($employee);
        }

        if (is_array($employee->working_days) && $employee->working_days !== []) {
            return $this->normalizeWorkingDays($employee->working_days);
        }

        if ($employee->department_id) {
            $department = $employee->relationLoaded('department')
                ? $employee->department
                : Department::query()->find($employee->department_id);

            if (is_array($department?->working_days) && $department->working_days !== []) {
                return $this->normalizeWorkingDays($department->working_days);
            }
        }

        if ($employee->sbu_id) {
            $sbu = $employee->relationLoaded('sbu')
                ? $employee->sbu
                : Sbu::query()->find($employee->sbu_id);

            if (is_array($sbu?->working_days) && $sbu->working_days !== []) {
                return $this->normalizeWorkingDays($sbu->working_days);
            }
        }

        if ($employee->organization_id) {
            $organization = $employee->relationLoaded('organization')
                ? $employee->organization
                : Organization::query()->find($employee->organization_id);

            if (is_array($organization?->working_days) && $organization->working_days !== []) {
                return $this->normalizeWorkingDays($organization->working_days);
            }
        }

        return null;
    }

    public function isHybridOnsiteDay(Carbon $date, Employee $employee): bool
    {
        return in_array($this->hybridDayKeyFromDate($date), $this->normalizeHybridDayKeys($employee->hybrid_days), true);
    }

    public function isHybridOffsiteDay(Carbon $date, Employee $employee): bool
    {
        return in_array($this->hybridDayKeyFromDate($date), $this->normalizeHybridDayKeys($employee->hybrid_offsite_days), true);
    }

    public function isWeeklyOffDay(Carbon $date, ?array $workingDays): bool
    {
        $dayKey = strtolower($date->format('l'));

        if ($workingDays) {
            return ! in_array($dayKey, $workingDays, true);
        }

        return $date->isSunday();
    }

    /**
     * @return array<int, string>|null
     */
    protected function resolveHybridWorkingDays(Employee $employee): ?array
    {
        $onsite = $this->normalizeHybridDaysToFull($employee->hybrid_days);
        $offsite = $this->normalizeHybridDaysToFull($employee->hybrid_offsite_days);
        $union = array_values(array_unique(array_merge($onsite, $offsite)));

        return $union === [] ? null : $union;
    }

    protected function hybridDayKeyFromDate(Carbon $date): string
    {
        return strtolower(substr($date->format('D'), 0, 3));
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeHybridDayKeys(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $day) {
            $key = is_string($day) ? strtolower(trim($day)) : '';
            if (in_array($key, self::HYBRID_DAY_KEYS, true)) {
                $out[] = $key;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeHybridDaysToFull(mixed $raw): array
    {
        $out = [];
        foreach ($this->normalizeHybridDayKeys($raw) as $shortKey) {
            $out[] = self::HYBRID_SHORT_TO_FULL[$shortKey];
        }

        return $out;
    }

    /**
     * @param  array<int, mixed>  $workingDays
     * @return array<int, string>
     */
    private function normalizeWorkingDays(array $workingDays): array
    {
        return array_values(array_map(static fn ($day) => strtolower((string) $day), $workingDays));
    }
}
