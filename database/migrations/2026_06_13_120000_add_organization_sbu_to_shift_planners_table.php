<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_planners', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained('organizations')
                ->nullOnDelete();
            $table->foreignId('sbu_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('sbus')
                ->nullOnDelete();
        });

        DB::table('shift_planners')
            ->select(['id', 'created_by'])
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    if (! $row->created_by) {
                        continue;
                    }

                    $employee = DB::table('users')
                        ->join('employees', 'employees.id', '=', 'users.employee_id')
                        ->where('users.id', $row->created_by)
                        ->select(['employees.organization_id', 'employees.sbu_id'])
                        ->first();

                    if ($employee === null) {
                        continue;
                    }

                    DB::table('shift_planners')
                        ->where('id', $row->id)
                        ->update([
                            'organization_id' => $employee->organization_id,
                            'sbu_id' => $employee->sbu_id,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('shift_planners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sbu_id');
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};
