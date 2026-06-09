<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class LeaveRequestOutstationService
{
    public const DESTINATION_PRESENT = 'present';

    public const DESTINATION_PERMANENT = 'permanent';

    public function getEmployeeDestinationAddresses(Employee $employee): array
    {
        $employee->loadMissing('contact');

        $present = trim((string) ($employee->contact?->present_address ?? ''));
        $permanent = trim((string) ($employee->contact?->permanent_address ?? ''));

        $destinations = [];

        if ($present !== '') {
            $destinations[] = $this->buildDestination(self::DESTINATION_PRESENT, 'Present Address', $present);
        }

        if ($permanent !== '') {
            $destinations[] = $this->buildDestination(self::DESTINATION_PERMANENT, 'Permanent Address', $permanent);
        }

        return [
            'present_address' => $present !== '' ? $present : null,
            'permanent_address' => $permanent !== '' ? $permanent : null,
            'destinations' => $destinations,
            'has_addresses' => $destinations !== [],
        ];
    }

    public function resolveExemptDays(Employee $employee, bool $isOutstation, ?string $destination): float
    {
        if (! $isOutstation || $destination === null || $destination === '') {
            return 0.0;
        }

        $address = $this->resolveDestinationAddress($employee, $destination);

        if ($address === null || $this->isInsideBaseCity($address)) {
            return 0.0;
        }

        return max(0.0, (float) config('hr.outstation_travel_exempt_days', 1));
    }

    public function assertOutstationSelectionValid(Employee $employee, bool $isOutstation, ?string $destination, bool $isHalfDay): void
    {
        if (! $isOutstation) {
            return;
        }

        if ($isHalfDay) {
            throw ValidationException::withMessages([
                'is_outstation_leave' => 'Outstation leave cannot be combined with short leave (half day).',
            ]);
        }

        if (! in_array($destination, [self::DESTINATION_PRESENT, self::DESTINATION_PERMANENT], true)) {
            throw ValidationException::withMessages([
                'outstation_destination' => 'Please select where you want to go for outstation leave.',
            ]);
        }

        $address = $this->resolveDestinationAddress($employee, $destination);

        if ($address === null || $address === '') {
            throw ValidationException::withMessages([
                'outstation_destination' => 'The selected address is not available on the employee profile. Please update employee registration first.',
            ]);
        }
    }

    public function billableDuration(float $grossDuration, float $exemptDays): float
    {
        return max(0.0, round($grossDuration - $exemptDays, 2));
    }

    public function destinationLabel(?string $destination): ?string
    {
        return match ($destination) {
            self::DESTINATION_PRESENT => 'Present Address',
            self::DESTINATION_PERMANENT => 'Permanent Address',
            default => null,
        };
    }

    public function isInsideBaseCity(string $address): bool
    {
        $normalized = strtolower(trim($address));

        if ($normalized === '') {
            return true;
        }

        $baseCity = strtolower(trim((string) config('hr.outstation_base_city', 'rawalpindi')));

        if ($baseCity !== '' && str_contains($normalized, $baseCity)) {
            return true;
        }

        return str_contains($normalized, 'rwp');
    }

    private function buildDestination(string $key, string $label, string $address): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'address' => $address,
            'outside_base_city' => ! $this->isInsideBaseCity($address),
        ];
    }

    private function resolveDestinationAddress(Employee $employee, string $destination): ?string
    {
        $employee->loadMissing('contact');
        $contact = $employee->contact;

        if ($contact === null) {
            return null;
        }

        return match ($destination) {
            self::DESTINATION_PRESENT => trim((string) ($contact->present_address ?? '')) ?: null,
            self::DESTINATION_PERMANENT => trim((string) ($contact->permanent_address ?? '')) ?: null,
            default => null,
        };
    }
}
