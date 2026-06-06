<?php

namespace App\Services;

use App\Models\ShiftPlanner;
use App\Models\ShiftRosterEntry;
use App\Models\ShiftRosterEntryEvent;
use Carbon\Carbon;

class ShiftRosterHistoryService
{
    private array $shiftNameCache = [];

    private const TRACKED_FIELDS = [
        'shift_planner_id',
        'is_custom_time',
        'start_time',
        'end_time',
        'floor',
        'location_text',
        'notes',
    ];

    public function recordCreated(ShiftRosterEntry $entry, ?int $userId): void
    {
        $entry->loadMissing(['employee.department', 'outsourcedEmployee']);

        $employeeName = $entry->employee?->full_name
            ?? $entry->outsourcedEmployee?->full_name
            ?? 'Employee';
        $departmentName = $entry->employee?->department?->name ?? '';

        $summary = 'Assigned to ' . $employeeName;
        if ($departmentName !== '') {
            $summary .= ' · ' . $departmentName;
        }

        $this->insertEvent($entry, ShiftRosterEntryEvent::EVENT_CREATED, $userId, $summary, []);
    }

    public function recordUpdated(ShiftRosterEntry $entry, array $before, ?int $userId): void
    {
        $changes = $this->buildChangeSet($before, $this->snapshot($entry));
        if ($changes === []) {
            return;
        }

        $this->insertEvent(
            $entry,
            ShiftRosterEntryEvent::EVENT_UPDATED,
            $userId,
            'Shift roster entry updated',
            $changes
        );
    }

    public function recordDeleted(ShiftRosterEntry $entry, ?int $userId): void
    {
        $this->insertEvent(
            $entry,
            ShiftRosterEntryEvent::EVENT_DELETED,
            $userId,
            'Shift roster entry removed',
            []
        );
    }

    public function snapshot(ShiftRosterEntry $entry): array
    {
        $values = [];
        foreach (self::TRACKED_FIELDS as $field) {
            $value = $entry->getAttribute($field);
            if ($value instanceof Carbon) {
                $value = $value->toDateString();
            }
            $values[$field] = $value;
        }

        return $values;
    }

    private function buildChangeSet(array $before, array $after): array
    {
        $changes = [];
        $skipEndTime = false;
        $timeRangeChanged = $this->valuesDiffer($before, $after, 'start_time')
            || $this->valuesDiffer($before, $after, 'end_time');

        foreach (self::TRACKED_FIELDS as $field) {
            if ($skipEndTime && $field === 'end_time') {
                continue;
            }

            $old = $this->normalizeValue($field, $before[$field] ?? null);
            $new = $this->normalizeValue($field, $after[$field] ?? null);

            if ($old === $new) {
                continue;
            }

            if ($timeRangeChanged && $field === 'start_time') {
                $changes[] = [
                    'field' => 'shift_time_range',
                    'label' => 'Shift time',
                    'before' => $this->formatTimeRange($before),
                    'after' => $this->formatTimeRange($after),
                ];
                $skipEndTime = true;
                continue;
            }

            if ($timeRangeChanged && $field === 'end_time') {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $this->fieldLabel($field),
                'before' => $this->formatStoredValue($field, $before[$field] ?? null),
                'after' => $this->formatStoredValue($field, $after[$field] ?? null),
            ];
        }

        return $changes;
    }

    private function formatTimeRange(array $state): string
    {
        $start = $this->formatStoredValue('start_time', $state['start_time'] ?? null);
        $end = $this->formatStoredValue('end_time', $state['end_time'] ?? null);

        if ($start === '' && $end === '') {
            return '';
        }

        if ($start === '') {
            return $end;
        }

        if ($end === '') {
            return $start;
        }

        return $start . ' – ' . $end;
    }

    private function valuesDiffer(array $before, array $after, string $field): bool
    {
        $old = $this->normalizeValue($field, $before[$field] ?? null);
        $new = $this->normalizeValue($field, $after[$field] ?? null);

        return $old !== $new;
    }

    private function normalizeValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($field === 'roster_date' && $value instanceof Carbon) {
            return $value->toDateString();
        }

        if (in_array($field, ['start_time', 'end_time'], true)) {
            try {
                return Carbon::parse($value)->format('H:i');
            } catch (\Throwable) {
                return '';
            }
        }

        if ($field === 'is_custom_time') {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'shift_planner_id' => 'Shift',
            'start_time', 'end_time' => 'Shift time',
            'floor' => 'Floor',
            'location_text' => 'Location',
            'notes' => 'Notes',
            'is_custom_time' => 'Custom time',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    private function formatStoredValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($field === 'shift_planner_id') {
            return $this->resolveShiftName((int) $value);
        }

        if (in_array($field, ['start_time', 'end_time'], true)) {
            try {
                return Carbon::parse($value)->format('h:i A');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($field === 'is_custom_time') {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    private function resolveShiftName(int $id): string
    {
        if ($id <= 0) {
            return '';
        }

        if (! array_key_exists($id, $this->shiftNameCache)) {
            $this->shiftNameCache[$id] = ShiftPlanner::query()->find($id)?->name ?? ('Shift #' . $id);
        }

        return $this->shiftNameCache[$id];
    }

    private function insertEvent(
        ShiftRosterEntry $entry,
        string $event,
        ?int $userId,
        ?string $summary,
        array $changes
    ): void {
        ShiftRosterEntryEvent::query()->create([
            'shift_roster_entry_id' => $entry->id,
            'event' => $event,
            'user_id' => $userId,
            'event_at' => now(),
            'summary' => $summary,
            'changes' => $changes === [] ? null : $changes,
        ]);
    }
}
