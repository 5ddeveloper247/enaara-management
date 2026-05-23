<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\ShiftRosterEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompensatoryLeaveAwardService
{
    public const REASON_PUBLIC_HOLIDAY = 'public_holiday';

    public const REASON_OFF_DAY = 'off_day';

    public function __construct(
        private readonly PublicHolidayResolver $publicHolidayResolver,
    ) {}

    public function resolveCompensatoryLeaveType(): ?LeaveType
    {
        return LeaveType::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereRaw('LOWER(code) = ?', ['cpl'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%compensatory%']);
            })
            ->orderByRaw("CASE WHEN LOWER(code) = 'cpl' THEN 0 ELSE 1 END")
            ->first();
    }

    public function isWorkShift(ShiftRosterEntry $entry): bool
    {
        $status = strtolower(trim((string) $entry->status));

        if (in_array($status, ['off', 'cancelled', 'holiday', 'blackout'], true)) {
            return false;
        }

        if ($entry->employee_id === null) {
            return false;
        }

        if ($entry->is_custom_time) {
            return $entry->start_time !== null && $entry->end_time !== null;
        }

        return $entry->shift_planner_id !== null;
    }

    public function isWorkShiftPayload(array $payload): bool
    {
        $status = strtolower(trim((string) ($payload['status'] ?? 'pending')));

        if (in_array($status, ['off', 'cancelled', 'holiday', 'blackout'], true)) {
            return false;
        }

        if (filter_var($payload['is_custom_time'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return ! empty($payload['start_time']) && ! empty($payload['end_time']);
        }

        return ! empty($payload['shift_planner_id']);
    }

    public function applyCompensatoryReasonToPayload(
        array &$payload,
        ?ShiftRosterEntry $existingEntry,
        ?Employee $employee,
        string $rosterDate
    ): void {
        if ($employee === null || ! $this->isWorkShiftPayload($payload)) {
            $payload['compensatory_reason'] = null;

            return;
        }

        if ($existingEntry !== null && strtolower((string) $existingEntry->status) === 'off') {
            $payload['compensatory_reason'] = self::REASON_OFF_DAY;

            return;
        }

        if ($this->publicHolidayResolver->resolveHolidayForEmployeeOnDate($employee, $rosterDate) !== null) {
            $payload['compensatory_reason'] = self::REASON_PUBLIC_HOLIDAY;

            return;
        }

        $payload['compensatory_reason'] = null;
    }

    public function resolveAwardReason(ShiftRosterEntry $entry): ?string
    {
        if (! $this->isWorkShift($entry) || $entry->employee === null) {
            return null;
        }

        $stamped = strtolower(trim((string) ($entry->compensatory_reason ?? '')));

        if ($stamped === self::REASON_OFF_DAY) {
            return self::REASON_OFF_DAY;
        }

        if ($stamped === self::REASON_PUBLIC_HOLIDAY) {
            return $this->publicHolidayResolver->resolveHolidayForEmployeeOnDate(
                $entry->employee,
                $entry->roster_date->toDateString()
            ) !== null
                ? self::REASON_PUBLIC_HOLIDAY
                : null;
        }

        if ($this->publicHolidayResolver->resolveHolidayForEmployeeOnDate(
            $entry->employee,
            $entry->roster_date->toDateString()
        ) !== null) {
            return self::REASON_PUBLIC_HOLIDAY;
        }

        return null;
    }

    public function awardForRosterEntry(ShiftRosterEntry $entry): bool
    {
        if ($entry->is_compensatory_earned) {
            return false;
        }

        $reason = $this->resolveAwardReason($entry);
        if ($reason === null) {
            return false;
        }

        $employee = $entry->employee;
        if ($employee === null) {
            return false;
        }

        $leaveType = $this->resolveCompensatoryLeaveType();
        if ($leaveType === null) {
            Log::warning('Compensatory leave type (code CPL/cpl) not found; roster entry skipped.', [
                'shift_roster_entry_id' => $entry->id,
            ]);

            return false;
        }

        $year = (int) $entry->roster_date->format('Y');

        DB::transaction(function () use ($employee, $leaveType, $year, $entry, $reason) {
            $quota = EmployeeLeaveQuota::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'department_id' => $employee->department_id,
                    'quota' => 0,
                    'used' => 0,
                ]
            );

            $quota->increment('quota', 1);

            $entry->is_compensatory_earned = true;
            $entry->compensatory_reason = $reason;
            $entry->status = $reason === self::REASON_OFF_DAY ? 'off_day_worked' : 'holiday_worked';
            $entry->save();
        });

        return true;
    }

    public function finalizedStatusForEntry(ShiftRosterEntry $entry): string
    {
        if (! $this->isWorkShift($entry)) {
            $status = strtolower(trim((string) $entry->status));

            return $status === 'off' ? 'off' : ($status === 'cancelled' ? 'cancelled' : 'used');
        }

        return 'used';
    }
}
