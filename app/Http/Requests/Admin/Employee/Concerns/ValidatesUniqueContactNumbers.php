<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use Illuminate\Support\Facades\DB;

trait ValidatesUniqueContactNumbers
{
    protected function assertUniqueContactNumbers($validator): void
    {
        $employeeId = $this->resolveEmployeeIdForContactUniqueness();
        $fields = ['residence_phone', 'emergency_contact', 'cell_no'];
        $normalized = [];

        foreach ($fields as $field) {
            $value = preg_replace('/[^\d+]/', '', (string) $this->input($field, ''));
            if ($value === '') {
                continue;
            }
            $normalized[$field] = $value;
        }

        if (empty($normalized)) {
            return;
        }

        foreach ($normalized as $field => $value) {
            $query = DB::table('employee_contacts')
                ->where(function ($q) use ($value) {
                    $q->where('residence_phone', $value)
                        ->orWhere('emergency_contact', $value)
                        ->orWhere('cell_no', $value);
                });

            if ($employeeId > 0) {
                $query->where('employee_id', '!=', $employeeId);
            }

            if ($query->exists()) {
                $validator->errors()->add($field, 'This number is already used by another employee.');
            }
        }
    }

    protected function resolveEmployeeIdForContactUniqueness(): int
    {
        $candidate = $this->input('employee_id');
        if ($candidate === null || $candidate === '') {
            $candidate = $this->route('employee');
        }
        if (is_object($candidate) && isset($candidate->id)) {
            $candidate = $candidate->id;
        }
        return (int) $candidate;
    }
}
