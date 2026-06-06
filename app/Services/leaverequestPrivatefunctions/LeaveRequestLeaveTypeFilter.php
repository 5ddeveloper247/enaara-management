<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
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
            ->values();
    }

    public function excludeCompensatoryFromList(Collection $leaveTypes): Collection
    {
        return $leaveTypes
            ->reject(fn ($type) => $this->isCompensatoryLeaveType($type))
            ->values();
    }

    public function filterQuotaSummary(array $quotaSummary, ?int $employeeId = null, ?int $year = null): array
    {
        $year = $year ?? (int) now()->year;
        $employee = $employeeId ? Employee::query()->find($employeeId) : null;

        return array_values(array_filter($quotaSummary, function (array $row) use ($employee, $employeeId, $year) {
            $leaveType = LeaveType::query()->with('setting')->find((int) ($row['id'] ?? 0));

            if ($leaveType === null) {
                return false;
            }

            if ($this->isMaternityLeaveType($leaveType)) {
                return $employee !== null && $this->isEmployeeEligibleForMaternityLeave($employee);
            }

            if (! $this->isCompensatoryLeaveType($leaveType)) {
                return true;
            }

            if ($employeeId === null) {
                return ((float) ($row['remaining'] ?? 0)) > 0;
            }

            return $this->compensatoryRemainingDays($employeeId, $year) > 0;
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

    private function shouldHideCompensatoryType($type, int $employeeId, int $year): bool
    {
        if (! $this->isCompensatoryLeaveType($type)) {
            return false;
        }

        return $this->compensatoryRemainingDays($employeeId, $year) <= 0;
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
