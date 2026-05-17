<?php

namespace App\Services;

use App\Models\ShiftRosterEntry;
use App\Models\ShiftRosterEntryEvent;
use Carbon\Carbon;

class ShiftRosterAuditHistoryService
{
    public function forEntry(int $entryId): array
    {
        $entry = ShiftRosterEntry::query()
            ->withTrashed()
            ->with(['shift'])
            ->findOrFail($entryId);

        $events = ShiftRosterEntryEvent::query()
            ->with('user')
            ->where('shift_roster_entry_id', $entryId)
            ->where('event_at', '>=', $entry->created_at)
            ->orderByDesc('event_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (ShiftRosterEntryEvent $event) => $this->mapEvent($event))
            ->values()
            ->all();

        return [
            'subtitle' => $this->buildSubtitle($entry),
            'stats' => $this->buildStats($events),
            'events' => $events,
        ];
    }

    private function mapEvent(ShiftRosterEntryEvent $event): array
    {
        $type = $event->event;

        return [
            'id' => 'event-' . $event->id,
            'type' => $type,
            'action' => $type,
            'actionLabel' => $this->actionLabel($type),
            'at' => $event->event_at?->toDateTimeString(),
            'userName' => $event->user?->name ?? 'System',
            'summary' => $event->summary ?? $this->defaultSummary($type),
            'description' => null,
            'changes' => $event->changes ?? [],
        ];
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
            if ($type === ShiftRosterEntryEvent::EVENT_CREATED) {
                $stats['created']++;
            } elseif ($type === ShiftRosterEntryEvent::EVENT_DELETED) {
                $stats['removed']++;
            } elseif ($type === ShiftRosterEntryEvent::EVENT_UPDATED) {
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

    private function actionLabel(string $type): string
    {
        return match ($type) {
            ShiftRosterEntryEvent::EVENT_CREATED => 'Created',
            ShiftRosterEntryEvent::EVENT_DELETED => 'Removed',
            default => 'Updated',
        };
    }

    private function defaultSummary(string $type): string
    {
        return match ($type) {
            ShiftRosterEntryEvent::EVENT_CREATED => 'Shift roster entry created',
            ShiftRosterEntryEvent::EVENT_DELETED => 'Shift roster entry removed',
            default => 'Shift roster entry updated',
        };
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
}
