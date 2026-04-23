<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_roster_entries', 'outsourced_employee_id')) {
                $table->foreignId('outsourced_employee_id')
                    ->nullable()
                    ->after('employee_id')
                    ->constrained('outsourced_employees')
                    ->nullOnDelete();
            }
        });

        DB::statement('ALTER TABLE shift_roster_entries MODIFY employee_id BIGINT UNSIGNED NULL');

        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->unique(
                ['outsourced_employee_id', 'roster_date', 'shift_planner_id'],
                'unique_outsourced_roster_entry'
            );
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->dropUnique('unique_outsourced_roster_entry');
            $table->dropConstrainedForeignId('outsourced_employee_id');
        });

        DB::statement('ALTER TABLE shift_roster_entries MODIFY employee_id BIGINT UNSIGNED NOT NULL');
    }
};
