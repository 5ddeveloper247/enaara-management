<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\LeaveType;
use Carbon\Carbon;

/**
 * Calculates join-date prorated annual leave quotas for unconditional leave types only.
 *
 * Example: annual_quota = 52, join_date = 2026-07-15 → remaining months Jul–Dec = 6
 *          prorated = 52 * 6 / 12 = 26
 *
 * Currently wired into EmployeeLeaveQuotaRecords (balance display + apply checks).
 * Other create paths still pending.
 */
class LeaveQuotaProrationService
{
    /**
     * Pure month-based proration for a calendar year.
     *
     * - Joined in a previous year → full annual quota
     * - Joined in the target year → annual_quota × remainingMonths / 12
     * - Joined after the target year → 0
     * - Missing join date → full annual quota
     *
     * Fractional results are rounded up to the next half-day (0.5) so leftover
     * balance is always usable as a full or half day leave.
     */
    public function calculate(float $annualQuota, Carbon|string|null $joinDate, ?int $year = null): float
    {
        $year = $year ?? (int) now()->year;
        $annualQuota = max(0.0, (float) $annualQuota);

        if ($annualQuota <= 0) {
            return 0.0;
        }

        if ($joinDate === null || $joinDate === '') {
            return $this->roundUpToHalfDay($annualQuota);
        }

        $join = $joinDate instanceof Carbon
            ? $joinDate->copy()->startOfDay()
            : Carbon::parse($joinDate)->startOfDay();

        $joinYear = (int) $join->year;

        if ($joinYear > $year) {
            return 0.0;
        }

        if ($joinYear < $year) {
            return $this->roundUpToHalfDay($annualQuota);
        }

        $remainingMonths = $this->remainingMonthsInYear($join, $year);
        $prorated = $annualQuota * $remainingMonths / 12;

        return $this->roundUpToHalfDay($prorated);
    }

    /**
     * Prorate using the employee's join_date and a raw annual quota value.
     */
    public function forEmployee(Employee $employee, float $annualQuota, ?int $year = null): float
    {
        return $this->calculate($annualQuota, $employee->join_date, $year);
    }

    /**
     * Prorate for a leave type.
     * Only unconditional leave types are prorated; CPL / conditional / general stay full annual_quota.
     */
    public function forLeaveType(Employee $employee, LeaveType $leaveType, ?int $year = null): float
    {
        $annualQuota = (float) $leaveType->annual_quota;

        if (! $this->shouldProrate($leaveType)) {
            return $this->roundUpToHalfDay(max(0.0, $annualQuota));
        }

        return $this->forEmployee($employee, $annualQuota, $year);
    }

    /**
     * Months from join month through December (inclusive) within the given year.
     * Returns 12 when join is before the year; 0 when join is after the year.
     */
    public function remainingMonthsInYear(Carbon|string|null $joinDate, ?int $year = null): int
    {
        $year = $year ?? (int) now()->year;

        if ($joinDate === null || $joinDate === '') {
            return 12;
        }

        $join = $joinDate instanceof Carbon
            ? $joinDate->copy()->startOfDay()
            : Carbon::parse($joinDate)->startOfDay();

        $joinYear = (int) $join->year;

        if ($joinYear > $year) {
            return 0;
        }

        if ($joinYear < $year) {
            return 12;
        }

        return 12 - (int) $join->month + 1;
    }

    /**
     * Only unconditional leave types are prorated.
     * Compensatory (CPL), conditional, and general leave types are not.
     */
    public function shouldProrate(LeaveType $leaveType): bool
    {
        $code = strtolower(trim((string) ($leaveType->code ?? '')));
        $name = strtolower(trim((string) ($leaveType->name ?? '')));

        if ($code === 'cpl' || str_contains($name, 'compensatory')) {
            return false;
        }

        return ($leaveType->leave_condition ?? '') === 'unconditional';
    }

    /**
     * Round up to the next half-day unit (0.5).
     * Examples: 6.25 → 6.5, 5.83 → 6.0, 5.5 → 5.5, 6.0 → 6.0
     */
    public function roundUpToHalfDay(float $days): float
    {
        if ($days <= 0) {
            return 0.0;
        }

        return round(ceil(round($days, 4) * 2) / 2, 2);
    }
}
