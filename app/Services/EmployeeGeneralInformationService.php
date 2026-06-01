<?php

namespace App\Services;

class EmployeeGeneralInformationService
{
    public static function composeFullName(?string $first, ?string $middle, ?string $last): string
    {
        $parts = array_filter([
            trim((string) $first),
            trim((string) $middle),
            trim((string) $last),
        ], static fn (string $part): bool => $part !== '');

        return implode(' ', $parts);
    }

    public function employeeAttributeNames(): array
    {
        return [
            'first_name',
            'middle_name',
            'last_name',
            'father_name',
            'cnic',
            'cnic_issue_date',
            'cnic_expiry',
            'father_cnic',
            'ntn',
            'gender',
            'nationality',
            'dob',
            'domicile_district',
            'domicile_province',
            'city_of_birth',
            'religion',
            'sect',
            'marital_status',
            'spouse_name',
            'spouse_cnic',
            'spouse_nationality',
            'nok_name',
            'nok_cnic',
            'nok_cnic_expiry_date',
            'nok_relation',
            'nok_dob',
            'nok_contact',
            'is_ex_armed_force',
            'is_father_deceased',
        ];
    }

    public function buildUpdatePayload(array $data): array
    {
        $payload = [];
        foreach ($this->employeeAttributeNames() as $field) {
            if (array_key_exists($field, $data)) {
                if ($field === 'is_ex_armed_force' || $field === 'is_father_deceased') {
                    $payload[$field] = (bool) $data[$field];
                } else {
                    $payload[$field] = $data[$field] === '' ? null : $data[$field];
                }
            }
        }

        if (
            array_key_exists('first_name', $payload)
            || array_key_exists('middle_name', $payload)
            || array_key_exists('last_name', $payload)
        ) {
            $payload['full_name'] = self::composeFullName(
                $payload['first_name'] ?? null,
                $payload['middle_name'] ?? null,
                $payload['last_name'] ?? null,
            );
        }

        return $payload;
    }
}
