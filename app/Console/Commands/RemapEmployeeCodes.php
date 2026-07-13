<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeIdSequence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemapEmployeeCodes extends Command
{
    protected $signature = 'employees:remap-codes
                            {--file= : Path to JSON mapping (defaults to database/data/employee_code_remap.json)}
                            {--dry-run : Show what would change without updating the database}
                            {--skip-remaining : Do not auto-assign ENR codes to employees missing from Excel}
                            {--skip-sequence : Do not sync entity_code_sequences after remap}';

    protected $description = 'Apply Excel ENR codes (from 10001), then continue the same sequence for any remaining employees (safe for production with extra staff).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $file = $this->option('file') ?: database_path('data/employee_code_remap.json');
        $prefix = strtoupper(trim((string) config('hr.employee_code_prefix', 'ENR')));

        if ($prefix === '') {
            $this->error('hr.employee_code_prefix is empty. Set HR_EMPLOYEE_CODE_PREFIX=ENR');

            return self::FAILURE;
        }

        if (! is_file($file)) {
            $this->error("Mapping file not found: {$file}");

            return self::FAILURE;
        }

        $mapping = json_decode((string) file_get_contents($file), true);
        if (! is_array($mapping) || $mapping === []) {
            $this->error('Mapping file is empty or invalid JSON.');

            return self::FAILURE;
        }

        $stats = [
            'excel_updated' => 0,
            'excel_already' => 0,
            'excel_missing' => 0,
            'excel_cnic_mismatch' => 0,
            'remaining_updated' => 0,
            'errors' => [],
        ];

        $runner = function () use ($mapping, $dryRun, $prefix, &$stats): void {
            $this->applyExcelMapping($mapping, $dryRun, $prefix, $stats);

            if (! $this->option('skip-remaining')) {
                $this->assignRemainingEmployees($dryRun, $prefix, $stats);
            }

            if (! $this->option('skip-sequence')) {
                $this->syncSequences($dryRun, $prefix);
            }
        };

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Using prefix {$prefix}; Excel rows: ".count($mapping));

        if ($dryRun) {
            $runner();
        } else {
            DB::transaction($runner);
        }

        $this->newLine();
        $this->info("Excel updated: {$stats['excel_updated']}");
        $this->info("Excel already on target: {$stats['excel_already']}");
        $this->info("Excel not found in DB: {$stats['excel_missing']}");
        $this->info("Excel CNIC mismatch skipped: {$stats['excel_cnic_mismatch']}");
        $this->info("Remaining auto-assigned: {$stats['remaining_updated']}");

        if ($stats['errors'] !== []) {
            $this->warn('Details:');
            foreach (array_slice($stats['errors'], 0, 40) as $error) {
                $this->line(' - '.$error);
            }
            if (count($stats['errors']) > 40) {
                $this->line(' - ... and '.(count($stats['errors']) - 40).' more');
            }
        }

        return self::SUCCESS;
    }

    /**
     * Excel employees keep the exact NEW ID from the sheet (ENR-10001, ENR-10002, ...).
     *
     * @param  array<int, array<string, mixed>>  $mapping
     * @param  array<string, mixed>  $stats
     */
    private function applyExcelMapping(array $mapping, bool $dryRun, string $prefix, array &$stats): void
    {
        $this->info(($dryRun ? '[DRY RUN] ' : '').'Step 1: apply Excel ENR codes exactly...');

        foreach ($mapping as $row) {
            $old = trim((string) ($row['old'] ?? ''));
            $new = trim((string) ($row['new'] ?? ''));
            $excelName = trim((string) ($row['full_name'] ?? ''));
            $excelCnic = $this->normalizeCnic($row['cnic'] ?? null);

            if ($old === '' || $new === '') {
                continue;
            }

            $employee = $this->findEmployeeForExcelRow($old, $excelName, $new);

            if (! $employee) {
                $stats['excel_missing']++;
                $stats['errors'][] = "Excel not found in DB: {$old} / {$excelName} → {$new}";
                continue;
            }

            $dbCnic = $this->normalizeCnic($employee->cnic);
            if ($excelCnic !== '' && $dbCnic !== '' && $excelCnic !== $dbCnic) {
                $stats['excel_cnic_mismatch']++;
                $stats['errors'][] = "CNIC mismatch for {$old} ({$excelName}): excel={$excelCnic} db={$dbCnic}";
                continue;
            }

            if ($employee->employee_code === $new) {
                $stats['excel_already']++;
                continue;
            }

            $taken = Employee::query()
                ->where('employee_code', $new)
                ->where('id', '!=', $employee->id)
                ->exists();

            if ($taken) {
                $stats['excel_missing']++;
                $stats['errors'][] = "Target already used: {$old} → {$new}";
                continue;
            }

            $this->line("[Excel] {$employee->employee_code} → {$new} (id={$employee->id}, {$employee->full_name})");

            if (! $dryRun) {
                $employee->employee_code = $new;
                $employee->save();
            }

            $stats['excel_updated']++;
        }
    }

    /**
     * Anyone still not on ENR- gets the next numbers in the same shared sequence.
     * Works whether production has 21 extras or 200 extras.
     *
     * @param  array<string, mixed>  $stats
     */
    private function assignRemainingEmployees(bool $dryRun, string $prefix, array &$stats): void
    {
        $this->info(($dryRun ? '[DRY RUN] ' : '').'Step 2: assign remaining employees on the same ENR sequence...');

        $nextNum = $this->highestEnrNumber($prefix) + 1;

        // In dry-run, excel updates are not persisted — reserve Excel target numbers too.
        if ($dryRun) {
            $nextNum = max($nextNum, $this->highestMappedNewNumber($prefix) + 1);
        }

        $remaining = Employee::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', '!=', '')
            ->where('employee_code', 'not like', $prefix.'-%')
            ->orderBy('id')
            ->get(['id', 'employee_code', 'full_name']);

        if ($remaining->isEmpty()) {
            $this->line('No remaining employees outside '.$prefix.'- series.');

            return;
        }

        foreach ($remaining as $employee) {
            $candidate = $prefix.'-'.$nextNum;
            while (
                Employee::query()
                    ->where('employee_code', $candidate)
                    ->when($dryRun, fn ($q) => $q) // still check DB
                    ->where('id', '!=', $employee->id)
                    ->exists()
            ) {
                $nextNum++;
                $candidate = $prefix.'-'.$nextNum;
            }

            $this->line("[Remaining] {$employee->employee_code} → {$candidate} (id={$employee->id}, {$employee->full_name})");

            if (! $dryRun) {
                $employee->employee_code = $candidate;
                $employee->save();
            }

            $stats['remaining_updated']++;
            $nextNum++;
        }
    }

    private function findEmployeeForExcelRow(string $oldCode, string $excelName, string $newCode): ?Employee
    {
        $byOld = Employee::query()->where('employee_code', $oldCode)->first();
        if ($byOld) {
            return $byOld;
        }

        // Already remapped on a previous run.
        $byNew = Employee::query()->where('employee_code', $newCode)->first();
        if ($byNew) {
            return $byNew;
        }

        // Production fallback: unique normalized name match.
        if ($excelName === '') {
            return null;
        }

        $normalized = $this->normalizeName($excelName);
        $matches = Employee::query()
            ->whereNotNull('full_name')
            ->get(['id', 'full_name', 'employee_code', 'cnic'])
            ->filter(fn (Employee $e) => $this->normalizeName((string) $e->full_name) === $normalized)
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function highestEnrNumber(string $prefix): int
    {
        return (int) Employee::query()
            ->whereNotNull('employee_code')
            ->where('employee_code', 'like', $prefix.'-%')
            ->get(['employee_code'])
            ->map(function (Employee $e) {
                $code = (string) $e->employee_code;
                $pos = strrpos($code, '-');

                return $pos === false ? 0 : (int) substr($code, $pos + 1);
            })
            ->max();
    }

    private function highestMappedNewNumber(string $prefix): int
    {
        $file = $this->option('file') ?: database_path('data/employee_code_remap.json');
        $mapping = json_decode((string) file_get_contents($file), true);
        if (! is_array($mapping)) {
            return 0;
        }

        $max = 0;
        foreach ($mapping as $row) {
            $new = trim((string) ($row['new'] ?? ''));
            if (! str_starts_with(strtoupper($new), $prefix.'-')) {
                continue;
            }
            $pos = strrpos($new, '-');
            if ($pos === false) {
                continue;
            }
            $max = max($max, (int) substr($new, $pos + 1));
        }

        return $max;
    }

    private function syncSequences(bool $dryRun, string $prefix): void
    {
        $maxNum = $this->highestEnrNumber($prefix);

        if ($dryRun) {
            $maxNum = max($maxNum, $this->highestMappedNewNumber($prefix));
            // Include dry-run remaining assignments count estimate from current non-prefix rows.
            $remainingCount = Employee::query()
                ->whereNotNull('employee_code')
                ->where('employee_code', '!=', '')
                ->where('employee_code', 'not like', $prefix.'-%')
                ->count();
            if (! $this->option('skip-remaining') && $remainingCount > 0) {
                $maxNum = max($maxNum, $this->highestMappedNewNumber($prefix) + $remainingCount);
            }
        }

        if ($maxNum <= 0) {
            $maxNum = 10001 - 1;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '')."Step 3: sync sequences → next new hire = {$prefix}-".($maxNum + 1));

        $sequences = EmployeeIdSequence::query()->get();
        if ($sequences->isEmpty()) {
            $this->warn('No rows in entity_code_sequences; create happens automatically on next employee create.');

            return;
        }

        foreach ($sequences as $seq) {
            $this->line("sequence sbu_id={$seq->sbu_id}: {$seq->prefix}-{$seq->last_number} → {$prefix}-{$maxNum}");
            if (! $dryRun) {
                $seq->prefix = $prefix;
                $seq->last_number = $maxNum;
                $seq->save();
            }
        }
    }

    private function normalizeCnic(mixed $cnic): string
    {
        return preg_replace('/\D+/', '', trim((string) ($cnic ?? ''))) ?? '';
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? ''));

        return $name;
    }
}
