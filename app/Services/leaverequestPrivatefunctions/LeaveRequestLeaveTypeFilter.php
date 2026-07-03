<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Services\CompensatoryLeaveAwardService;
use App\Services\CompensatoryLeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveRequestLeaveTypeFilter
{
    public function __construct(
        private CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
        private CompensatoryLeaveBalanceService $compensatoryLeaveBalanceService,
    ) {}

    public function filterForEmployee(Collection $leaveTypes, int $employeeId, ?int $year = null): Collection
    {
        $year = $year ?? (int) now()->year;
        $employee = Employee::query()->find($employeeId);

        return $leaveTypes
            ->reject(fn ($type) => $this->shouldHideCompensatoryType($type, $employeeId, $year))
            ->reject(fn ($type) => $this->shouldHideMaternityLeaveType($type, $employee))
            ->reject(fn ($type) => $this->shouldHidePaternityLeaveType($type, $employee))
            ->values();
    }

    public function excludeCompensatoryFromList(Collection $leaveTypes): Collection
    {
        return $leaveTypes
            ->reject(fn ($type) => $this->isCompensatoryLeaveType($type))
            ->values();
    }

    /**
     * Whether an employee may have balance for this leave type, based on leave type setting gender only.
     */
    public function isEmployeeEligibleByLeaveTypeGender(Employee $employee, LeaveType $leaveType): bool
    {
        $leaveType->loadMissing('setting');
        $required = strtolower(trim((string) ($leaveType->setting?->gender ?? 'all')));

        if ($required === '' || $required === 'all') {
            return true;
        }

        $actual = strtolower(trim((string) ($employee->gender ?? '')));

        if ($actual === '') {
            return false;
        }

        return $actual === $required;
    }

    public function leaveTypeGenderEligibilityMessage(Employee $employee, LeaveType $leaveType): ?string
    {
        if ($this->isEmployeeEligibleByLeaveTypeGender($employee, $leaveType)) {
            return null;
        }

        $leaveType->loadMissing('setting');
        $required = strtolower(trim((string) ($leaveType->setting?->gender ?? 'all')));
        $requiredLabel = $this->genderLabelForMessage($required);
        $actual = strtolower(trim((string) ($employee->gender ?? '')));

        if ($actual === '') {
            return "This leave type is only for {$requiredLabel} employees. Employee gender is not set on their profile.";
        }

        return "This leave type is only for {$requiredLabel} employees.";
    }

    /**
     * Same eligibility rules as My Leaves quota summary (maternity, paternity, compensatory, etc.).
     */
    public function isEmployeeEligibleForQuotaDisplay(
        Employee $employee,
        LeaveType $leaveType,
        ?int $year = null,
        ?EmployeeLeaveQuota $quota = null,
    ): bool {
        $year = $year ?? (int) now()->year;
        $leaveType->loadMissing('setting');

        if ($this->isMaternityLeaveType($leaveType)) {
            return $this->isEmployeeEligibleForMaternityLeave($employee);
        }

        if ($this->isPaternityLeaveType($leaveType)) {
            return $this->isEmployeeEligibleForPaternityLeave($employee, $leaveType);
        }

        if ($this->isCompensatoryLeaveType($leaveType)) {
            return $this->isEmployeeEligibleForCompensatoryDisplay($employee->id, $year, $quota);
        }

        return true;
    }

    public function isEmployeeEligibleForCompensatoryDisplay(
        int $employeeId,
        ?int $year = null,
        ?EmployeeLeaveQuota $quota = null,
    ): bool {
        if ($this->compensatoryLeaveBalanceService->validEarnedDays($employeeId) > 0) {
            return true;
        }

        if ($this->compensatoryRemainingDays($employeeId, $year) > 0) {
            return true;
        }

        if ($quota !== null && ((float) $quota->adjusted_quota > 0 || (float) $quota->used > 0)) {
            return true;
        }

        return false;
    }

    /**
     * @return array{earned: float, used: float, remaining: float}
     */
    public function buildCompensatoryQuotaSnapshot(
        int $employeeId,
        ?int $year = null,
        ?EmployeeLeaveQuota $quota = null,
    ): array {
        $year = $year ?? (int) now()->year;
        $asOf = Carbon::today();

        $earned = $this->compensatoryLeaveBalanceService->validEarnedDays($employeeId, $asOf);
        $remaining = $this->compensatoryLeaveBalanceService->remainingDays($employeeId, $year, $asOf);
        $used = max(0.0, $earned - $remaining);

        return [
            'earned' => $earned,
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    public function isCompensatoryLeaveTypeId(int $leaveTypeId): bool
    {
        return $this->compensatoryLeaveBalanceService->isCompensatoryLeaveTypeId($leaveTypeId);
    }

    public function requiresSupportingDocument(LeaveType $leaveType): bool
    {
        if (($leaveType->leave_condition ?? '') !== 'conditional') {
            return false;
        }

        return ! $this->isCompensatoryLeaveType($leaveType);
    }

    public function quotaDisplayEligibilityMessage(
        Employee $employee,
        LeaveType $leaveType,
        ?int $year = null,
        ?EmployeeLeaveQuota $quota = null,
    ): ?string {
        if ($this->isEmployeeEligibleForQuotaDisplay($employee, $leaveType, $year, $quota)) {
            return null;
        }

        $leaveType->loadMissing('setting');

        if ($this->isMaternityLeaveType($leaveType)) {
            $gender = strtolower(trim((string) ($employee->gender ?? '')));

            if ($gender !== 'female') {
                return 'Maternity leave is only available for female employees.';
            }

            return 'Maternity leave is only available for married female employees.';
        }

        if ($this->isPaternityLeaveType($leaveType)) {
            $requiredGender = $this->requiredGenderForPaternityLeave($leaveType);
            $requiredLabel = $this->genderLabelForMessage($requiredGender ?? 'male');
            $actualGender = strtolower(trim((string) ($employee->gender ?? '')));

            if ($requiredGender !== null && $actualGender !== $requiredGender) {
                return "Paternity leave is only available for {$requiredLabel} employees.";
            }

            return "Paternity leave is only available for married {$requiredLabel} employees.";
        }

        if ($this->isCompensatoryLeaveType($leaveType)) {
            return 'Compensatory leave is not available. The employee has no earned or assigned compensatory quota.';
        }

        return 'This employee is not eligible for this leave type.';
    }

    public function filterQuotaSummary(array $quotaSummary, ?int $employeeId = null, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;

        return array_values(array_filter($quotaSummary, function (array $row) use ($employee, $year) {
            $leaveType = LeaveType::query()->with('setting')->find((int) ($row['id'] ?? 0));

            if ($leaveType === null || $employee === null) {
                return false;
            }

            $quota = EmployeeLeaveQuota::query()
                ->where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->first();

            return $this->isEmployeeEligibleForQuotaDisplay($employee, $leaveType, $year, $quota);
        }));
    }

    public function compensatoryRemainingDays(int $employeeId, ?int $year = null): float
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        if ($cplType === null) {
            return 0.0;
        }

        $year = $year ?? (int) now()->year;

        return $this->compensatoryLeaveBalanceService->remainingDays($employeeId, $year);
    }

    public function assertCompensatoryLeaveAllowed(Employee $employee, LeaveType $leaveType, Carbon $startDate): void
    {
        if (! $this->isCompensatoryLeaveType($leaveType)) {
            return;
        }

        if ($this->compensatoryRemainingDays($employee->id, (int) $startDate->year) <= 0) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Compensatory leave is not available. You have no balance, or earned days older than '
                    .CompensatoryLeaveBalanceService::EXPIRY_DAYS.' days have expired.',
            ]);
        }
    }

    public function assertMaternityLeaveAllowed(Employee $employee, LeaveType $leaveType): void
    {
        if (! $this->isMaternityLeaveType($leaveType)) {
            return;
        }

        if ($this->isEmployeeEligibleForMaternityLeave($employee)) {
            return;
        }

        $gender = strtolower(trim((string) ($employee->gender ?? '')));

        if ($gender !== 'female') {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Maternity leave is only available for female employees.',
            ]);
        }

        throw ValidationException::withMessages([
            'leave_type_id' => 'Maternity leave is only available for married female employees.',
        ]);
    }

    public function isEmployeeEligibleForMaternityLeave(Employee $employee): bool
    {
        $gender = strtolower(trim((string) ($employee->gender ?? '')));
        $maritalStatus = trim((string) ($employee->marital_status ?? ''));

        return $gender === 'female' && strcasecmp($maritalStatus, 'Married') === 0;
    }

    public function assertPaternityLeaveAllowed(Employee $employee, LeaveType $leaveType): void
    {
        if (! $this->isPaternityLeaveType($leaveType)) {
            return;
        }

        if ($this->isEmployeeEligibleForPaternityLeave($employee, $leaveType)) {
            return;
        }

        $requiredGender = $this->requiredGenderForPaternityLeave($leaveType);
        $requiredLabel = $this->genderLabelForMessage($requiredGender ?? 'male');
        $actualGender = strtolower(trim((string) ($employee->gender ?? '')));

        if ($requiredGender !== null && $actualGender !== $requiredGender) {
            throw ValidationException::withMessages([
                'leave_type_id' => "Paternity leave is only available for {$requiredLabel} employees.",
            ]);
        }

        throw ValidationException::withMessages([
            'leave_type_id' => "Paternity leave is only available for married {$requiredLabel} employees.",
        ]);
    }

    public function isEmployeeEligibleForPaternityLeave(Employee $employee, $type): bool
    {
        $requiredGender = $this->requiredGenderForPaternityLeave($type);

        if ($requiredGender === null) {
            return false;
        }

        $actualGender = strtolower(trim((string) ($employee->gender ?? '')));
        $maritalStatus = trim((string) ($employee->marital_status ?? ''));

        return $actualGender === $requiredGender && strcasecmp($maritalStatus, 'Married') === 0;
    }

    private function shouldHideCompensatoryType($type, int $employeeId, int $year): bool
    {
        if (! $this->isCompensatoryLeaveType($type)) {
            return false;
        }

        $quota = EmployeeLeaveQuota::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $type->id)
            ->where('year', $year)
            ->first();

        return ! $this->isEmployeeEligibleForCompensatoryDisplay($employeeId, $year, $quota);
    }

    private function shouldHideMaternityLeaveType($type, ?Employee $employee): bool
    {
        if (! $this->isMaternityLeaveType($type)) {
            return false;
        }

        if ($employee === null) {
            return true;
        }

        return ! $this->isEmployeeEligibleForMaternityLeave($employee);
    }

    private function shouldHidePaternityLeaveType($type, ?Employee $employee): bool
    {
        if (! $this->isPaternityLeaveType($type)) {
            return false;
        }

        if ($employee === null) {
            return true;
        }

        return ! $this->isEmployeeEligibleForPaternityLeave($employee, $type);
    }

    private function isMaternityLeaveType($type): bool
    {
        $code = strtoupper(trim((string) ($type->code ?? '')));

        if ($code === 'ML') {
            return true;
        }

        $name = strtolower(trim((string) ($type->name ?? '')));

        if (! str_contains($name, 'maternity')) {
            return false;
        }

        if ($type instanceof LeaveType && ! $type->relationLoaded('setting')) {
            $type->loadMissing('setting');
        }

        return strtolower(trim((string) ($type->setting?->gender ?? ''))) === 'female';
    }

    private function isPaternityLeaveType($type): bool
    {
        $code = strtoupper(trim((string) ($type->code ?? '')));

        if ($code === 'PL') {
            return true;
        }

        $name = strtolower(trim((string) ($type->name ?? '')));

        if (! str_contains($name, 'paternity')) {
            return false;
        }

        return $this->requiredGenderForPaternityLeave($type) !== null;
    }

    private function requiredGenderForPaternityLeave($type): ?string
    {
        if ($type instanceof LeaveType && ! $type->relationLoaded('setting')) {
            $type->loadMissing('setting');
        }

        $gender = strtolower(trim((string) ($type->setting?->gender ?? '')));

        if (in_array($gender, ['male', 'female'], true)) {
            return $gender;
        }

        if (strtoupper(trim((string) ($type->code ?? ''))) === 'PL') {
            return 'male';
        }

        return null;
    }

    private function genderLabelForMessage(string $gender): string
    {
        return match (strtolower($gender)) {
            'male' => 'male',
            'female' => 'female',
            default => $gender,
        };
    }

    private function isCompensatoryLeaveType($type): bool
    {
        $cplType = $this->compensatoryLeaveAwardService->resolveCompensatoryLeaveType();

        if ($cplType !== null && (int) $type->id === (int) $cplType->id) {
            return true;
        }

        $name = strtolower(trim((string) ($type->name ?? '')));
        $code = strtolower(trim((string) ($type->code ?? '')));

        return str_contains($name, 'compensatory') || $code === 'cpl';
    }
}
