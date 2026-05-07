<?php

namespace App\Services;

class EmployeeGeneralInformationService
{
    public function employeeAttributeNames(): array
    {
        return [
            'full_name',
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

        return $payload;
    }
}
