<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use App\Models\Designation;
use Closure;

trait ValidatesEmployeeDesignationId
{
    protected function designationIdRules(): array
    {
        return [
            'designation_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $orgId = (int) $this->input('organization_id');
                    $sbuId = (int) $this->input('sbu_id');
                    $departmentId = (int) ($this->input('department_id') ?: 0);
                    if ($departmentId <= 0) {
                        $deptIds = $this->input('department_ids', []);
                        if (is_array($deptIds) && ! empty($deptIds)) {
                            $departmentId = (int) $deptIds[0];
                        }
                    }

                    if ($value === null || $value === '' || (int) $value === 0) {
                        return;
                    }
                    if ($sbuId <= 0 || $orgId <= 0) {
                        $fail('Select organization and SBU before choosing a designation.');

                        return;
                    }
                    if ($departmentId <= 0) {
                        $fail('Select a department before choosing a designation.');

                        return;
                    }
                    $exists = Designation::query()
                        ->whereKey((int) $value)
                        ->where('organization_id', $orgId)
                        ->where('sbu_id', $sbuId)
                        ->where('department_id', $departmentId)
                        ->where('is_active', true)
                        ->exists();
                    if (! $exists) {
                        $fail('The selected designation is not valid for this organization, SBU, and department.');
                    }
                },
            ],
        ];
    }
}
