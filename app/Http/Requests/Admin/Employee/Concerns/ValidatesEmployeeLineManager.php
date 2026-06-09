<?php

namespace App\Http\Requests\Admin\Employee\Concerns;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Validation\Validator;

trait ValidatesEmployeeLineManager
{
    protected function lineManagerRules(): array
    {
        return [
            'is_manager' => ['nullable', 'boolean'],
        ];
    }

    protected function mergeLineManagerBoolean(): void
    {
        $this->merge([
            'is_manager' => $this->boolean('is_manager'),
        ]);
    }

    protected function assertUniqueDepartmentLineManager(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        if (! $this->boolean('is_manager')) {
            return;
        }

        $departmentId = (int) ($this->input('department_id') ?: 0);
        if ($departmentId <= 0) {
            $validator->errors()->add(
                'is_manager',
                'Please select a department before marking this employee as line manager.'
            );

            return;
        }

        $existing = $this->findConflictingDepartmentLineManager($departmentId, $this->resolveLineManagerExcludeEmployeeId());

        if ($existing) {
            $deptName = $existing->department?->name
                ?? Department::query()->where('id', $departmentId)->value('name')
                ?? 'this department';

            $validator->errors()->add(
                'is_manager',
                sprintf(
                    '%s is already the line manager for %s. Please remove that assignment first before assigning another employee.',
                    $existing->full_name,
                    $deptName
                )
            );
        }
    }

    protected function resolveLineManagerExcludeEmployeeId(): ?int
    {
        $id = $this->input('employee_id') ?? $this->route('id');

        return $id ? (int) $id : null;
    }

    protected function findConflictingDepartmentLineManager(int $departmentId, ?int $excludeEmployeeId = null): ?Employee
    {
        return Employee::query()
            ->where('is_manager', true)
            ->where('is_active', true)
            ->where('department_id', $departmentId)
            ->when($excludeEmployeeId, fn ($query) => $query->where('id', '!=', $excludeEmployeeId))
            ->with('department:id,name')
            ->first(['id', 'full_name', 'employee_code', 'department_id']);
    }
}
