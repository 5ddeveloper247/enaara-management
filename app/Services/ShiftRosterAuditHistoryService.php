<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterEntry;
use Carbon\Carbon;

class ShiftRosterAuditHistoryService
{
    private const HIDDEN_FIELDS = [
        'updated_at',
        'created_at',
        'updated_by',
        'created_by',
        'assigned_by',
        'deleted_by',
        'assignment_id',
        'deleted_at',
    ];

    private const FIELD_LABELS = [
        'shift_planner_id' => 'Shift',
        'start_time' => 'Shift time',
        'end_time' => 'Shift time',
        'floor' => 'Floor',
        'location_text' => 'Location',
        'notes' => 'Notes',
        'is_custom_time' => 'Custom time',
        'status' => 'Status',
        'late_check_in' => 'Late check-in',
        'check_in' => 'Check-in',
        'check_out' => 'Check-out',
        'roster_date' => 'Roster date',
        'employee_id' => 'Employee',
        'outsourced_employee_id' => 'Employee',
        'is_compensatory_earned' => 'Compensatory off',
    ];

    private array $shiftNameCache = [];

    public function forEntry(int $entryId): array
    {
        $entry = ShiftRosterEntry::query()
            ->withTrashed()
            ->with(['shift', 'employee.department', 'outsourcedEmployee', 'createdBy', 'assignedBy', 'deletedBy'])
            ->findOrFail($entryId);

        $employeeName = $entry->employee?->full_name
            ?? $entry->outsourcedEmployee?->full_name
            ?? 'Employee';
        $departmentName = $entry->employee?->department?->name ?? '';

        $auditEvents = AuditTrail::query()
            ->with(['changes', 'user'])
            ->where('auditable_type', ShiftRosterEntry::class)
            ->where('auditable_id', $entryId)
            ->orderByDesc('action_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (AuditTrail $trail) => $this->mapAuditTrailEvent($trail))
            ->filter()
            ->values()
            ->all();

        $events = $this->mergeEntryMetaEvents($entry, $employeeName, $departmentName, $auditEvents);
        $events = $this->deduplicateEvents($events);
        $events = $this->sortEvents($events);

        return [
            'subtitle' => $this->buildSubtitle($entry),
            'stats' => $this->buildStats($events),
            'events' => $events,
        ];
    }

    private function mapAuditTrailEvent(AuditTrail $trail): ?array
    {
        $type = $this->normalizeEventType($trail->action);
        $changes = $this->formatTrailChanges($trail);

        if ($changes === [] && ! in_array($type, ['created', 'deleted'], true)) {
            return null;
        }

        return [
            'id' => 'audit-' . $trail->id,
            'type' => $type,
            'action' => $trail->action,
            'actionLabel' => $this->actionLabel($trail->action, $type),
            'at' => $trail->action_at?->toDateTimeString(),
            'userName' => $trail->user?->name ?? 'System',
            'summary' => $this->defaultSummary($type, $trail->description),
            'description' => $trail->description,
            'changes' => $changes,
        ];
    }

    private function deduplicateEvents(array $events): array
    {
        $unique = [];

        foreach ($events as $event) {
            $changesKey = md5(json_encode($event['changes'] ?? []));
            $atKey = $event['at'] ? Carbon::parse($event['at'])->format('Y-m-d H:i') : '';
            $key = ($event['type'] ?? '')
                . '|' . $atKey
                . '|' . ($event['userName'] ?? '')
                . '|' . ($event['summary'] ?? '')
                . '|' . $changesKey;

            if (! isset($unique[$key])) {
                $unique[$key] = $event;
            }
        }

        return array_values($unique);
    }

    private function mergeEntryMetaEvents(
        ShiftRosterEntry $entry,
        string $employeeName,
        ?string $departmentName,
        array $auditEvents
    ): array {
        $hasCreated = AuditTrail::query()
            ->where('auditable_type', ShiftRosterEntry::class)
            ->where('auditable_id', $entry->id)
            ->where('action', 'created')
            ->exists();
        $hasAssigned = collect($auditEvents)->contains(fn (array $e) => $e['type'] === 'assigned');
        $hasDeleted = AuditTrail::query()
            ->where('auditable_type', ShiftRosterEntry::class)
            ->where('auditable_id', $entry->id)
            ->where('action', 'deleted')
            ->exists();

        $meta = [];

        if (! $hasCreated && $entry->created_by && $entry->created_at) {
            $meta[] = [
                'id' => 'meta-created',
                'type' => 'created',
                'action' => 'created',
                'actionLabel' => 'Created',
                'at' => $entry->created_at->toDateTimeString(),
                'userName' => $entry->createdBy?->name ?? 'System',
                'summary' => 'Shift roster entry created',
                'description' => null,
                'changes' => [],
            ];
        }

        if (! $hasAssigned && $entry->assigned_by) {
            $assignedAt = $entry->assigned_by ? $entry->created_at : null;
            $summary = 'Assigned to ' . $employeeName;
            if ($departmentName) {
                $summary .= ' · ' . $departmentName;
            }

            $meta[] = [
                'id' => 'meta-assigned',
                'type' => 'assigned',
                'action' => 'assigned',
                'actionLabel' => 'Assigned',
                'at' => $assignedAt?->toDateTimeString(),
                'userName' => $entry->assignedBy?->name ?? 'System',
                'summary' => $summary,
                'description' => null,
                'changes' => [],
            ];
        }

        if (! $hasDeleted && $entry->deleted_by && $entry->deleted_at) {
            $meta[] = [
                'id' => 'meta-deleted',
                'type' => 'deleted',
                'action' => 'deleted',
                'actionLabel' => 'Removed',
                'at' => $entry->deleted_at->toDateTimeString(),
                'userName' => $entry->deletedBy?->name ?? 'System',
                'summary' => 'Shift roster entry removed',
                'description' => null,
                'changes' => [],
            ];
        }

        return array_merge($auditEvents, $meta);
    }

    private function sortEvents(array $events): array
    {
        usort($events, function (array $a, array $b) {
            return strcmp((string) ($b['at'] ?? ''), (string) ($a['at'] ?? ''));
        });

        return array_values($events);
    }

    private function buildStats(array $events): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'assigned' => 0,
            'removed' => 0,
        ];

        foreach ($events as $event) {
            $type = $event['type'] ?? '';
            if ($type === 'created') {
                $stats['created']++;
            } elseif ($type === 'assigned') {
                $stats['assigned']++;
            } elseif ($type === 'deleted') {
                $stats['removed']++;
            } elseif ($type === 'updated') {
                $stats['updated']++;
            }
        }

        return $stats;
    }

    private function buildSubtitle(ShiftRosterEntry $entry): string
    {
        if (strtolower((string) $entry->status) === 'off') {
            return 'Shift roster entry · Off day';
        }

        if ((bool) $entry->is_custom_time) {
            $start = $this->formatTimeValue($entry->start_time);
            $end = $this->formatTimeValue($entry->end_time);

            return 'Shift roster entry · Custom · ' . $start . ' – ' . $end;
        }

        $shiftName = $entry->shift?->name;
        if ($shiftName) {
            return 'Shift roster entry · ' . $shiftName;
        }

        return 'Shift roster entry';
    }

    private function normalizeEventType(?string $action): string
    {
        $action = strtolower((string) $action);

        return match ($action) {
            'created' => 'created',
            'deleted' => 'deleted',
            default => 'updated',
        };
    }

    private function actionLabel(?string $action, string $type): string
    {
        return match ($type) {
            'created' => 'Created',
            'assigned' => 'Assigned',
            'deleted' => 'Removed',
            default => 'Updated',
        };
    }

    private function defaultSummary(string $type, ?string $description): string
    {
        if ($description && strlen($description) < 120) {
            return $description;
        }

        return match ($type) {
            'created' => 'Shift roster entry created',
            'assigned' => 'Shift assigned',
            'deleted' => 'Shift roster entry removed',
            default => 'Shift roster entry updated',
        };
    }

    private function formatTrailChanges(AuditTrail $trail): array
    {
        $formatted = [];
        $seenLabels = [];

        foreach ($trail->changes as $change) {
            $field = (string) $change->field;

            if (in_array($field, self::HIDDEN_FIELDS, true)) {
                continue;
            }

            $label = self::FIELD_LABELS[$field] ?? ucfirst(str_replace('_', ' ', $field));
            if ($field === 'end_time' && isset($seenLabels['Shift time'])) {
                continue;
            }

            $before = $this->formatFieldValue($field, $change->old_value);
            $after = $this->formatFieldValue($field, $change->new_value);

            if ($before === $after) {
                continue;
            }

            if ($field === 'start_time' && $trail->changes->contains(fn ($c) => $c->field === 'end_time')) {
                $endChange = $trail->changes->firstWhere('field', 'end_time');
                if ($endChange) {
                    $before = $this->formatFieldValue('start_time', $change->old_value)
                        . ' – ' . $this->formatFieldValue('end_time', $endChange->old_value);
                    $after = $this->formatFieldValue('start_time', $change->new_value)
                        . ' – ' . $this->formatFieldValue('end_time', $endChange->new_value);
                    $label = 'Shift time';
                }
            }

            $seenLabels[$label] = true;

            $formatted[] = [
                'field' => $field,
                'label' => $label,
                'before' => $before,
                'after' => $after,
            ];
        }

        return $formatted;
    }

    private function formatFieldValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $normalized = is_string($value) ? trim($value) : $value;

        if ($field === 'shift_planner_id') {
            return $this->resolveShiftName($normalized);
        }

        if (in_array($field, ['start_time', 'end_time', 'check_in', 'check_out'], true)) {
            return $this->formatTimeValue($normalized);
        }

        if ($field === 'roster_date') {
            try {
                return Carbon::parse($normalized)->format('d M Y');
            } catch (\Throwable) {
                return (string) $normalized;
            }
        }

        if ($field === 'is_custom_time' || $field === 'late_check_in' || $field === 'is_compensatory_earned') {
            return $this->formatBooleanValue($normalized);
        }

        if ($field === 'status') {
            return ucfirst(str_replace('_', ' ', strtolower((string) $normalized)));
        }

        return (string) $normalized;
    }

    private function resolveShiftName(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $id = (int) $value;
        if ($id <= 0) {
            return '';
        }

        if (! array_key_exists($id, $this->shiftNameCache)) {
            $this->shiftNameCache[$id] = ShiftPlanner::query()->find($id)?->name ?? ('Shift #' . $id);
        }

        return $this->shiftNameCache[$id];
    }

    private function formatTimeValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('h:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatBooleanValue(mixed $value): string
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes'], true)) {
            return 'Yes';
        }

        if (in_array($normalized, ['0', 'false', 'no', ''], true)) {
            return 'No';
        }

        return (string) $value;
    }
}
