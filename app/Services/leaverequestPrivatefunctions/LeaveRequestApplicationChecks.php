<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LeaveRequestApplicationChecks
{
    public function __construct(
        private LeaveRequestLeaveTypeFilter $leaveRequestLeaveTypeFilter,
    ) {}

    public function assertEligibleForApplication(
        Employee $employee,
        LeaveType $leaveType,
        Carbon $startDate,
        Carbon $endDate,
        float $durationDays
    ): void {
        $this->leaveRequestLeaveTypeFilter->assertCompensatoryLeaveAllowed($employee, $leaveType, $startDate);
        $this->leaveRequestLeaveTypeFilter->assertMaternityLeaveAllowed($employee, $leaveType);

        $leaveType->loadMissing('setting');
        $setting = $leaveType->setting;

        if ($leaveType->is_active !== true) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'This leave type is not active.',
            ]);
        }

        if ($setting === null) {
            return;
        }

        if (($setting->unit_of_leave ?? 'days') === 'hours') {
            throw ValidationException::withMessages([
                'leave_type_id' => 'This leave type is configured for hourly leave; full-day requests are not supported yet.',
            ]);
        }

        $this->assertEmploymentTypeMatches($employee, $setting->employment_type ?? 'all');
        $this->assertGenderMatches($employee, $setting->gender ?? 'all');
        $this->assertProbationEligibility($employee, (bool) ($setting->probation_eligible ?? false));
        $this->assertMaxConsecutiveDays($setting->max_consecutive_days, $durationDays);
        $this->assertAdvanceNotice($startDate, (int) ($setting->advance_notice_days ?? 0));
        $this->assertOnceInTenureRestriction($employee, $leaveType, $setting->accrual_frequency ?? null);
    }

    private function assertOnceInTenureRestriction(Employee $employee, LeaveType $leaveType, ?string $accrualFrequency): void
    {
        if ($accrualFrequency !== 'once_in_tenure') {
            return;
        }

        $hasExisting = \App\Models\EmployeLeaveRequest::query()
            ->where('from_employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->whereNotIn('status', [4, 5]) // 4 = rejected, 5 = cancelled
            ->exists();

        if ($hasExisting) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'You can only apply for this leave type once in your entire tenure, and you have already submitted a request.',
            ]);
        }
    }

    private function assertEmploymentTypeMatches(Employee $employee, string $required): void
    {
        if ($required === 'all') {
            return;
        }

        $actual = strtolower(trim((string) ($employee->employment_type ?? '')));

        if ($actual === '') {
            throw ValidationException::withMessages([
                'employee_id' => 'Your employment type is not set; this leave type cannot be validated.',
            ]);
        }

        if ($actual !== strtolower($required)) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'This leave type is not available for your employment type.',
            ]);
        }
    }

    private function assertGenderMatches(Employee $employee, string $required): void
    {
        if ($required === 'all') {
            return;
        }

        $requiredNorm = strtolower(trim($required));
        $actual = strtolower(trim((string) ($employee->gender ?? '')));
        $requiredLabel = $this->genderLabelForMessage($requiredNorm);

        if ($actual === '') {
            throw ValidationException::withMessages([
                'employee_id' => "This leave type is only for {$requiredLabel} employees. Your gender is missing on your profile—please update it, then try again.",
            ]);
        }

        if ($actual !== $requiredNorm) {
            $actualLabel = $this->genderLabelForMessage($actual);

            throw ValidationException::withMessages([
                'leave_type_id' => "This leave type is only for {$requiredLabel} employees. Your profile is recorded as {$actualLabel}, so this leave cannot be applied.",
            ]);
        }
    }

    private function genderLabelForMessage(string $code): string
    {
        return match ($code) {
            'male' => 'Male',
            'female' => 'Female',
            default => ucfirst($code),
        };
    }

    private function assertProbationEligibility(Employee $employee, bool $probationEligible): void
    {
        if ($probationEligible) {
            return;
        }

        if (! $this->employeeIsOnProbation($employee)) {
            return;
        }

        throw ValidationException::withMessages([
            'leave_type_id' => 'This leave type cannot be applied during probation.',
        ]);
    }

    private function employeeIsOnProbation(Employee $employee): bool
    {
        $employment = strtolower(trim((string) ($employee->employment_type ?? '')));

        if ($employment === 'probation') {
            return true;
        }

        $today = Carbon::today();
        $start = $employee->probation_start_date;
        $end = $employee->probation_end_date;

        if ($start !== null && $end !== null && $today->betweenIncluded($start, $end)) {
            return true;
        }

        return false;
    }

    private function assertMaxConsecutiveDays(?int $maxConsecutive, float $durationDays): void
    {
        if ($maxConsecutive === null || $maxConsecutive <= 0) {
            return;
        }

        if ($durationDays > $maxConsecutive) {
            throw ValidationException::withMessages([
                'end_date' => "This leave type allows at most {$maxConsecutive} consecutive day(s). Reduce the duration.",
            ]);
        }
    }

    private function assertAdvanceNotice(Carbon $startDate, int $advanceNoticeDays): void
    {
        if ($advanceNoticeDays <= 0) {
            return;
        }

        $today = Carbon::today();
        $minimumStart = $today->copy()->addDays($advanceNoticeDays);

        if ($startDate->copy()->startOfDay()->lt($minimumStart)) {
            throw ValidationException::withMessages([
                'start_date' => "This leave type requires at least {$advanceNoticeDays} calendar day(s) advance notice.",
            ]);
        }
    }
}
