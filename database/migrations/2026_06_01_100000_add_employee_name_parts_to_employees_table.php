<?php

use App\Services\EmployeeGeneralInformationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'first_name')) {
                $table->string('first_name', 50)->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('employees', 'middle_name')) {
                $table->string('middle_name', 50)->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('employees', 'last_name')) {
                $table->string('last_name', 50)->nullable()->after('middle_name');
            }
        });

        $rows = DB::table('employees')
            ->where(function ($q) {
                $q->whereNull('first_name')->orWhere('first_name', '');
            })
            ->whereNotNull('full_name')
            ->where('full_name', '!=', '')
            ->get(['id', 'full_name']);

        foreach ($rows as $row) {
            $parts = $this->splitFullName((string) $row->full_name);
            DB::table('employees')->where('id', $row->id)->update([
                'first_name'  => $parts['first_name'],
                'middle_name' => $parts['middle_name'],
                'last_name'   => $parts['last_name'],
                'full_name'   => EmployeeGeneralInformationService::composeFullName(
                    $parts['first_name'],
                    $parts['middle_name'],
                    $parts['last_name']
                ),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('employees', 'middle_name')) {
                $table->dropColumn('middle_name');
            }
            if (Schema::hasColumn('employees', 'first_name')) {
                $table->dropColumn('first_name');
            }
        });
    }

    private function splitFullName(string $fullName): array
    {
        $tokens = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return ['first_name' => null, 'middle_name' => null, 'last_name' => null];
        }

        if (count($tokens) === 1) {
            return [
                'first_name'  => $tokens[0],
                'middle_name' => null,
                'last_name'   => $tokens[0],
            ];
        }

        if (count($tokens) === 2) {
            return [
                'first_name'  => $tokens[0],
                'middle_name' => null,
                'last_name'   => $tokens[1],
            ];
        }

        return [
            'first_name'  => array_shift($tokens),
            'last_name'   => array_pop($tokens),
            'middle_name' => implode(' ', $tokens) ?: null,
        ];
    }
};
