<?php

namespace App\Services;

use App\Models\EmployeLeaveEntity;

/**
 * Pure classification for leave cells on the shift roster grid and exports.
 * No database access — callers must eager-load leaveRequest (+ leaveType) when needed.
 */
final class RosterLeaveCellResolver
{
    public const SHIFT_WEEKLY_REST = 'weekly_rest';

    public const SHIFT_HALF_LEAVE = 'half_leave';

    public const SHIFT_LEAVE = 'leave';

    /**
     * @return array{
     *     shiftType: string,
     *     isWeeklyRest: bool,
     *     isHalfDayLeave: bool,
     *     isLeave: bool,
     *     isOffDay: bool,
     *     leaveName: string,
     * }
     */
    public static function fromEntity(EmployeLeaveEntity $entity): array
    {
        $request = $entity->relationLoaded('leaveRequest') ? $entity->leaveRequest : null;
        $leaveName = $request?->leaveType?->name
            ?? ($entity->relationLoaded('leaveType') ? $entity->leaveType?->name : null)
            ?? 'Leave';

        return self::classify(
            duration: (float) $entity->duration,
            countsAgainstQuota: (bool) ($entity->counts_against_quota ?? true),
            isHalfDayRequest: (bool) ($request?->is_half_day ?? false),
            isOutstationLeave: (bool) ($request?->is_outstation_leave ?? false),
            leaveTypeName: $leaveName,
        );
    }

    /**
     * @return array{
     *     shiftType: string,
     *     isWeeklyRest: bool,
     *     isHalfDayLeave: bool,
     *     isLeave: bool,
     *     isOffDay: bool,
     *     leaveName: string,
     * }
     */
    public static function classify(
        float $duration,
        bool $countsAgainstQuota,
        bool $isHalfDayRequest,
        bool $isOutstationLeave,
        string $leaveTypeName = 'Leave',
    ): array {
        $isWeeklyRest = $isOutstationLeave && ! $countsAgainstQuota;
        $isHalfDayLeave = ! $isWeeklyRest
            && ($isHalfDayRequest || ($duration > 0 && $duration < 1.0));

        if ($isWeeklyRest) {
            return [
                'shiftType' => self::SHIFT_WEEKLY_REST,
                'isWeeklyRest' => true,
                'isHalfDayLeave' => false,
                'isLeave' => true,
                'isOffDay' => true,
                'leaveName' => 'Weekly Rest',
            ];
        }

        if ($isHalfDayLeave) {
            return [
                'shiftType' => self::SHIFT_HALF_LEAVE,
                'isWeeklyRest' => false,
                'isHalfDayLeave' => true,
                'isLeave' => true,
                'isOffDay' => false,
                'leaveName' => $leaveTypeName,
            ];
        }

        return [
            'shiftType' => self::SHIFT_LEAVE,
            'isWeeklyRest' => false,
            'isHalfDayLeave' => false,
            'isLeave' => true,
            'isOffDay' => true,
            'leaveName' => $leaveTypeName,
        ];
    }

    public static function exportLabel(array $resolved): string
    {
        return match ($resolved['shiftType']) {
            self::SHIFT_WEEKLY_REST => 'Weekly Rest',
            self::SHIFT_HALF_LEAVE => 'Short Leave',
            default => $resolved['leaveName'],
        };
    }
}
