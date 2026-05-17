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
            if ($this->indexExists('shift_roster_entries', 'unique_employee_roster_day')) {
                $table->dropUnique('unique_employee_roster_day');
            }
            if ($this->indexExists('shift_roster_entries', 'unique_outsourced_roster_day')) {
                $table->dropUnique('unique_outsourced_roster_day');
            }
        });

        if (! Schema::hasColumn('shift_roster_entries', 'active_employee_day_key')) {
            DB::statement("
                ALTER TABLE shift_roster_entries
                ADD COLUMN active_employee_day_key VARCHAR(32) AS (
                    IF(
                        deleted_at IS NULL AND employee_id IS NOT NULL,
                        CONCAT(employee_id, '-', roster_date),
                        NULL
                    )
                ) STORED
            ");
        }

        if (! Schema::hasColumn('shift_roster_entries', 'active_outsourced_day_key')) {
            DB::statement("
                ALTER TABLE shift_roster_entries
                ADD COLUMN active_outsourced_day_key VARCHAR(32) AS (
                    IF(
                        deleted_at IS NULL AND outsourced_employee_id IS NOT NULL,
                        CONCAT(outsourced_employee_id, '-', roster_date),
                        NULL
                    )
                ) STORED
            ");
        }

        if (! $this->indexExists('shift_roster_entries', 'unique_active_employee_roster_day')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->unique('active_employee_day_key', 'unique_active_employee_roster_day');
            });
        }

        if (! $this->indexExists('shift_roster_entries', 'unique_active_outsourced_roster_day')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->unique('active_outsourced_day_key', 'unique_active_outsourced_roster_day');
            });
        }
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if ($this->indexExists('shift_roster_entries', 'unique_active_employee_roster_day')) {
                $table->dropUnique('unique_active_employee_roster_day');
            }
            if ($this->indexExists('shift_roster_entries', 'unique_active_outsourced_roster_day')) {
                $table->dropUnique('unique_active_outsourced_roster_day');
            }
        });

        if (Schema::hasColumn('shift_roster_entries', 'active_employee_day_key')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->dropColumn('active_employee_day_key');
            });
        }

        if (Schema::hasColumn('shift_roster_entries', 'active_outsourced_day_key')) {
            Schema::table('shift_roster_entries', function (Blueprint $table) {
                $table->dropColumn('active_outsourced_day_key');
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
};
