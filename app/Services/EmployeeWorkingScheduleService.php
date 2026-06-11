<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Sbu;
use Carbon\Carbon;

class EmployeeWorkingScheduleService
{
    public function isShiftBased(Employee $employee): bool
    {
        return strtolower(trim((string) ($employee->engagement_mode ?? ''))) === 'shifts';
    }

    public function resolveWorkingDays(Employee $employee): ?array
    {
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

    public function isWeeklyOffDay(Carbon $date, ?array $workingDays): bool
    {
        $dayKey = strtolower($date->format('l'));

        if ($workingDays) {
            return ! in_array($dayKey, $workingDays, true);
        }

        return $date->isSunday();
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
