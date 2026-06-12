<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

trait ValidatesHybridWorkSchedule
{
    protected function assertHybridWorkScheduleRules(\Illuminate\Validation\Validator $validator): void
    {
        if ($this->input('engagement_mode') !== 'hybrid') {
            return;
        }

        $onsite = $this->normalizeHybridDayKeys($this->input('hybrid_days'));
        $offsite = $this->normalizeHybridDayKeys($this->input('hybrid_offsite_days'));

        if ($onsite === [] && $offsite === []) {
            $validator->errors()->add(
                'hybrid_days',
                'Select at least one on-site or off-site day when work arrangement is Hybrid.'
            );

            return;
        }

        $overlap = array_values(array_intersect($onsite, $offsite));
        if ($overlap !== []) {
            $labels = array_map(fn (string $day) => $this->hybridDayLabel($day), $overlap);
            $validator->errors()->add(
                'hybrid_offsite_days',
                'A day cannot be both on-site and off-site: ' . implode(', ', $labels) . '.'
            );
        }
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeHybridDayKeys(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $allowed = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $out = [];

        foreach ($raw as $day) {
            $key = is_string($day) ? strtolower(trim($day)) : '';
            if (in_array($key, $allowed, true)) {
                $out[] = $key;
            }
        }

        return array_values(array_unique($out));
    }

    protected function hybridDayLabel(string $dayKey): string
    {
        return match ($dayKey) {
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
            'sat' => 'Sat',
            'sun' => 'Sun',
            default => ucfirst($dayKey),
        };
    }
}
