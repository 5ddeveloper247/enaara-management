<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Console\Command;

class SyncEmployeePrimaryDepartmentsFromDesignations extends Command
{
    protected $signature = 'employees:sync-primary-departments {--dry-run : Preview changes without saving}';

    protected $description = 'Set employees.department_id from their assigned designation department';

    public function handle(EmployeeService $employeeService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $skipped = 0;

        Employee::query()
            ->whereNotNull('designation_id')
            ->orderBy('id')
            ->chunkById(100, function ($employees) use ($employeeService, $dryRun, &$updated, &$skipped) {
                foreach ($employees as $employee) {
                    $before = (int) ($employee->department_id ?? 0);

                    if ($dryRun) {
                        $designation = \App\Models\Designation::query()->find((int) $employee->designation_id);
                        $target = $designation?->department_id ? (int) $designation->department_id : null;

                        if ($target === null || $target === $before) {
                            $skipped++;
                            continue;
                        }

                        $this->line(sprintf(
                            'Would update #%d %s: department_id %d -> %d (%s)',
                            $employee->id,
                            $employee->full_name ?? $employee->employee_code,
                            $before,
                            $target,
                            $designation?->name ?? 'designation'
                        ));
                        $updated++;

                        continue;
                    }

                    $result = $employeeService->syncEmployeePrimaryDepartmentFromDesignation($employee, true);
                    if ($result === null || $result === $before) {
                        $skipped++;
                        continue;
                    }

                    $this->line(sprintf(
                        'Updated #%d %s: department_id %d -> %d',
                        $employee->id,
                        $employee->full_name ?? $employee->employee_code,
                        $before,
                        $result
                    ));
                    $updated++;
                }
            });

        $this->info(sprintf(
            '%s complete. Updated: %d, already correct/skipped: %d',
            $dryRun ? 'Dry run' : 'Sync',
            $updated,
            $skipped
        ));

        return self::SUCCESS;
    }
}
