<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Services\CompensatoryLeaveAwardService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class LeaveRequestLeaveTypeFilter
{
    public function __construct(
        private CompensatoryLeaveAwardService $compensatoryLeaveAwardService,
        private EmployeeLeaveQuotaRecords $employeeLeaveQuotaRecords,
    ) {}

    public function filterForEmployee(Collection $leaveTypes, int $employeeId, ?int $year = null): Collection
    {
        $year = $year ?? (int) now()->year;

        return $leaveTypes
            ->reject(fn ($type) => $this->shouldHideCompensatoryType($type, $employeeId, $year))
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

        return array_values(array_filter($quotaSummary, function (array $row) use ($employeeId, $year) {
            $leaveType = LeaveType::query()->find((int) ($row['id'] ?? 0));

            if ($leaveType === null || ! $this->isCompensatoryLeaveType($leaveType)) {
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

        return $this->employeeLeaveQuotaRecords->earnedLeaveRemainingDays(
            $employeeId,
            (int) $cplType->id,
            $year
        );
    }

    public function assertCompensatoryLeaveAllowed(Employee $employee, LeaveType $leaveType, Carbon $startDate): void
    {
        if (! $this->isCompensatoryLeaveType($leaveType)) {
            return;
        }

        if ($this->compensatoryRemainingDays($employee->id, (int) $startDate->year) <= 0) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'Compensatory leave is not available. You have no compensatory leave balance.',
            ]);
        }
    }

    private function shouldHideCompensatoryType($type, int $employeeId, int $year): bool
    {
        if (! $this->isCompensatoryLeaveType($type)) {
            return false;
        }

        return $this->compensatoryRemainingDays($employeeId, $year) <= 0;
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
