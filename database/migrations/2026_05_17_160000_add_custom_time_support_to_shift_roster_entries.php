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
            if (! Schema::hasColumn('shift_roster_entries', 'is_custom_time')) {
                $table->boolean('is_custom_time')->default(false)->after('shift_planner_id');
            }
        });

        if (! $this->indexExists('shift_roster_entries', 'shift_roster_entries_employee_roster_date_index')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->index(['employee_id', 'roster_date'], 'shift_roster_entries_employee_roster_date_index');
            });
        }

        if (! $this->indexExists('shift_roster_entries', 'shift_roster_entries_outsourced_roster_date_index')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->index(['outsourced_employee_id', 'roster_date'], 'shift_roster_entries_outsourced_roster_date_index');
            });
        }

        if ($this->foreignKeyExists('shift_roster_entries', 'shift_roster_entries_shift_planner_id_foreign')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->dropForeign(['shift_planner_id']);
            });
        }

        if ($this->indexExists('shift_roster_entries', 'unique_roster_entry')) {
            DB::statement('ALTER TABLE shift_roster_entries DROP INDEX unique_roster_entry');
        }

        if ($this->indexExists('shift_roster_entries', 'unique_outsourced_roster_entry')) {
            DB::statement('ALTER TABLE shift_roster_entries DROP INDEX unique_outsourced_roster_entry');
        }

        DB::statement('ALTER TABLE shift_roster_entries MODIFY shift_planner_id BIGINT UNSIGNED NULL');

        if (! $this->foreignKeyExists('shift_roster_entries', 'shift_roster_entries_shift_planner_id_foreign')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->foreign('shift_planner_id')
                    ->references('id')
                    ->on('shift_planners')
                    ->nullOnDelete();
            });
        }

        if (! $this->indexExists('shift_roster_entries', 'unique_employee_roster_day')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->unique(['employee_id', 'roster_date'], 'unique_employee_roster_day');
            });
        }

        if (! $this->indexExists('shift_roster_entries', 'unique_outsourced_roster_day')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->unique(['outsourced_employee_id', 'roster_date'], 'unique_outsourced_roster_day');
            });
        }

        DB::statement('
            UPDATE shift_roster_entries e
            INNER JOIN shift_planners s ON e.shift_planner_id = s.id
            SET e.start_time = COALESCE(e.start_time, s.start_time),
                e.end_time = COALESCE(e.end_time, s.end_time)
            WHERE e.status != \'off\' AND e.is_custom_time = 0 AND e.shift_planner_id IS NOT NULL
        ');

        DB::statement('
            UPDATE shift_roster_entries e
            INNER JOIN shift_planners s ON e.shift_planner_id = s.id
            SET e.is_custom_time = 1, e.shift_planner_id = NULL
            WHERE e.status != \'off\'
              AND e.start_time IS NOT NULL
              AND e.end_time IS NOT NULL
              AND (
                  TIME(e.start_time) != TIME(s.start_time)
                  OR TIME(e.end_time) != TIME(s.end_time)
              )
        ');

        $this->dropAssignmentTypeCheckIfExists();
    }

    private function dropAssignmentTypeCheckIfExists(): void
    {
        if ($this->checkConstraintExists('shift_roster_entries', 'chk_shift_roster_assignment_type')) {
            DB::statement('ALTER TABLE shift_roster_entries DROP CHECK chk_shift_roster_assignment_type');
        }
    }

    public function down(): void
    {
        $this->dropAssignmentTypeCheckIfExists();

        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if ($this->indexExists('shift_roster_entries', 'unique_employee_roster_day')) {
                $table->dropUnique('unique_employee_roster_day');
            }
            if ($this->indexExists('shift_roster_entries', 'unique_outsourced_roster_day')) {
                $table->dropUnique('unique_outsourced_roster_day');
            }
        });

        DB::table('shift_roster_entries')
            ->where('is_custom_time', true)
            ->whereNull('shift_planner_id')
            ->delete();

        if ($this->foreignKeyExists('shift_roster_entries', 'shift_roster_entries_shift_planner_id_foreign')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->dropForeign(['shift_planner_id']);
            });
        }

        DB::statement('ALTER TABLE shift_roster_entries MODIFY shift_planner_id BIGINT UNSIGNED NOT NULL');

        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->foreign('shift_planner_id')
                ->references('id')
                ->on('shift_planners')
                ->cascadeOnDelete();

            $table->unique(['employee_id', 'roster_date', 'shift_planner_id'], 'unique_roster_entry');
            $table->unique(['outsourced_employee_id', 'roster_date', 'shift_planner_id'], 'unique_outsourced_roster_entry');

            $table->dropColumn('is_custom_time');
        });

        if ($this->indexExists('shift_roster_entries', 'shift_roster_entries_employee_roster_date_index')) {
            DB::statement('ALTER TABLE shift_roster_entries DROP INDEX shift_roster_entries_employee_roster_date_index');
        }

        if ($this->indexExists('shift_roster_entries', 'shift_roster_entries_outsourced_roster_date_index')) {
            DB::statement('ALTER TABLE shift_roster_entries DROP INDEX shift_roster_entries_outsourced_roster_date_index');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS aggregate FROM information_schema.table_constraints WHERE table_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ?',
            [$database, $table, $constraint, 'FOREIGN KEY']
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function checkConstraintExists(string $table, string $constraint): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(1) AS aggregate FROM information_schema.table_constraints WHERE table_schema = ? AND table_name = ? AND constraint_name = ?',
            [$database, $table, $constraint]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
