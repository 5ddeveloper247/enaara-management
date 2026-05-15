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
                    if ($value === null || $value === '' || (int) $value === 0) {
                        return;
                    }
                    $orgId = (int) $this->input('organization_id');
                    $sbuId = (int) $this->input('sbu_id');
                    if ($sbuId <= 0 || $orgId <= 0) {
                        $fail('Select organization and SBU before choosing a designation.');

                        return;
                    }
                    $exists = Designation::query()
                        ->whereKey((int) $value)
                        ->where('organization_id', $orgId)
                        ->where('sbu_id', $sbuId)
                        ->where('is_active', true)
                        ->exists();
                    if (! $exists) {
                        $fail('The selected designation is not valid for this organization and SBU.');
                    }
                },
            ],
        ];
    }
}
