<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OutsourcedEmployee;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class PublicHolidayResolver
{
    public function appliesToAssignee(
        PublicHoliday $holiday,
        ?int $organizationId,
        ?int $departmentId,
        ?int $sbuId
    ): bool {
        if (! $this->matchesOrganizationScope($holiday, $organizationId)) {
            return false;
        }

        if (! $this->matchesDepartmentScope($holiday, $departmentId)) {
            return false;
        }

        if (! $this->matchesSbuScope($holiday, $sbuId)) {
            return false;
        }

        return true;
    }

    public function resolveHolidayForEmployeeOnDate(Employee $employee, string $date): ?PublicHoliday
    {
        $target = Carbon::parse($date)->startOfDay();
        $holidays = $this->loadHolidaysForRange($target, $target);

        return $this->resolveForAssigneeOnDate(
            $holidays,
            $employee->organization_id ? (int) $employee->organization_id : null,
            $employee->department_id ? (int) $employee->department_id : null,
            $employee->sbu_id ? (int) $employee->sbu_id : null,
            $target->toDateString()
        );
    }

    public function resolveForAssigneeOnDate(
        Collection $holidays,
        ?int $organizationId,
        ?int $departmentId,
        ?int $sbuId,
        string $date
    ): ?PublicHoliday {
        foreach ($holidays as $holiday) {
            if (! $this->holidayOccursOnDate($holiday, $date)) {
                continue;
            }

            if ($this->appliesToAssignee($holiday, $organizationId, $departmentId, $sbuId)) {
                return $holiday;
            }
        }

        return null;
    }

    public function loadHolidaysForRange(Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        $start = $rangeStart->copy()->startOfDay();
        $end = $rangeEnd->copy()->endOfDay();

        return PublicHoliday::query()
            ->where('is_blackout', false)
            ->with([
                'organizations:id',
                'departments:id',
                'sbus:id',
            ])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($nonRecurring) use ($start, $end) {
                    $nonRecurring->where('is_recurring', false)
                        ->whereDate('start_date', '<=', $end)
                        ->whereDate('end_date', '>=', $start);
                })->orWhere('is_recurring', true);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, Employee>  $employees
     * @param  Collection<int, OutsourcedEmployee>  $outsourcedEmployees
     * @return array<int, array<string, mixed>>
     */
    public function buildVirtualRosterHolidaysForGrid(
        Collection $employees,
        Collection $outsourcedEmployees,
        Carbon $rangeStart,
        Carbon $rangeEnd
    ): array {
        $holidays = $this->loadHolidaysForRange($rangeStart, $rangeEnd);
        if ($holidays->isEmpty()) {
            return [];
        }

        $virtualHolidayKeys = [];
        $virtual = [];
        $period = CarbonPeriod::create(
            $rangeStart->copy()->startOfDay(),
            $rangeEnd->copy()->startOfDay()
        );

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            foreach ($employees as $employee) {
                $employeeKey = 'employee:' . $employee->id;
                $cellKey = $employeeKey . '|' . $dateString;

                if (isset($virtualHolidayKeys[$cellKey])) {
                    continue;
                }

                $holiday = $this->resolveForAssigneeOnDate(
                    $holidays,
                    $employee->organization_id ? (int) $employee->organization_id : null,
                    $employee->department_id ? (int) $employee->department_id : null,
                    $employee->sbu_id ? (int) $employee->sbu_id : null,
                    $dateString
                );

                if ($holiday === null) {
                    continue;
                }

                $virtual[] = $this->formatVirtualRosterRow($employeeKey, 'employee', $employee->id, $dateString, $holiday);
                $virtualHolidayKeys[$cellKey] = true;
            }

            foreach ($outsourcedEmployees as $outsourced) {
                $employeeKey = 'outsourced:' . $outsourced->id;
                $cellKey = $employeeKey . '|' . $dateString;

                if (isset($virtualHolidayKeys[$cellKey])) {
                    continue;
                }

                $holiday = $this->resolveForAssigneeOnDate(
                    $holidays,
                    $outsourced->organization_id ? (int) $outsourced->organization_id : null,
                    null,
                    $outsourced->sbu_id ? (int) $outsourced->sbu_id : null,
                    $dateString
                );

                if ($holiday === null) {
                    continue;
                }

                $virtual[] = $this->formatVirtualRosterRow($employeeKey, 'outsourced', $outsourced->id, $dateString, $holiday);
                $virtualHolidayKeys[$cellKey] = true;
            }
        }

        return $virtual;
    }

    public function holidayOccursOnDate(PublicHoliday $holiday, string $date): bool
    {
        $target = Carbon::parse($date)->startOfDay();

        if ($holiday->is_recurring) {
            $occurrenceStart = Carbon::createFromDate(
                $target->year,
                $holiday->start_date->month,
                $holiday->start_date->day
            )->startOfDay();
            $occurrenceEnd = Carbon::createFromDate(
                $target->year,
                $holiday->end_date->month,
                $holiday->end_date->day
            )->startOfDay();

            if ($occurrenceEnd->lt($occurrenceStart)) {
                return $target->gte($occurrenceStart) || $target->lte($occurrenceEnd);
            }

            return $target->betweenIncluded($occurrenceStart, $occurrenceEnd);
        }

        $start = $holiday->start_date->copy()->startOfDay();
        $end = $holiday->end_date->copy()->startOfDay();

        return $target->betweenIncluded($start, $end);
    }

    private function matchesOrganizationScope(PublicHoliday $holiday, ?int $organizationId): bool
    {
        if ($holiday->organization_scope === 'all') {
            return true;
        }

        if ($holiday->organization_scope !== 'specific') {
            return false;
        }

        if ($organizationId === null || $organizationId <= 0) {
            return false;
        }

        return $holiday->organizations->contains('id', $organizationId);
    }

    private function matchesDepartmentScope(PublicHoliday $holiday, ?int $departmentId): bool
    {
        $scope = $holiday->department_scope ?? 'none';

        if ($scope === 'none' || $scope === 'all') {
            return true;
        }

        if ($scope !== 'specific') {
            return false;
        }

        if ($departmentId === null || $departmentId <= 0) {
            return false;
        }

        return $holiday->departments->contains('id', $departmentId);
    }

    private function matchesSbuScope(PublicHoliday $holiday, ?int $sbuId): bool
    {
        $scope = $holiday->sbu_scope ?? 'none';

        if ($scope === 'none' || $scope === 'all') {
            return true;
        }

        if ($scope !== 'specific') {
            return false;
        }

        if ($sbuId === null || $sbuId <= 0) {
            return false;
        }

        return $holiday->sbus->contains('id', $sbuId);
    }

    private function formatVirtualRosterRow(
        string $employeeKey,
        string $employeeType,
        int $sourceId,
        string $dateString,
        PublicHoliday $holiday
    ): array {
        $day = (int) Carbon::parse($dateString)->format('d');

        return [
            'rosterId' => null,
            'employeeId' => $employeeKey,
            'employeeType' => $employeeType,
            'sourceId' => $sourceId,
            'rosterDate' => $dateString,
            'day' => $day,
            'shiftPlannerId' => null,
            'isCustomTime' => false,
            'shiftType' => 'holiday',
            'timeStart' => null,
            'timeEnd' => null,
            'floor' => null,
            'location' => null,
            'notes' => null,
            'sbuFloorId' => null,
            'status' => 'holiday',
            'isOffDay' => true,
            'isPublicHoliday' => true,
            'holidayName' => $holiday->name,
            'isCompensatory' => false,
            'deletedAt' => null,
            'createdAt' => null,
            'updatedAt' => null,
            'assignedAt' => null,
            'createdByName' => null,
            'updatedByName' => null,
            'assignedByName' => null,
            'deletedByName' => null,
        ];
    }
}
