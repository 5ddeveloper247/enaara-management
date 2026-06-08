<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leave_type_settings') && ! Schema::hasColumn('leave_type_settings', 'half_day_applicable')) {
            Schema::table('leave_type_settings', function (Blueprint $table) {
                $table->boolean('half_day_applicable')->default(false)->after('short_leave_max_hours');
            });
        }

        if (Schema::hasTable('employe_leave_requests')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('employe_leave_requests', 'is_half_day')) {
                    $table->boolean('is_half_day')->default(false)->after('duration');
                }
                if (! Schema::hasColumn('employe_leave_requests', 'half_day_session')) {
                    $table->string('half_day_session', 20)->nullable()->after('is_half_day');
                }
            });
        }

        if (Schema::hasTable('employe_leave_entities') && ! Schema::hasColumn('employe_leave_entities', 'half_day_session')) {
            Schema::table('employe_leave_entities', function (Blueprint $table) {
                $table->string('half_day_session', 20)->nullable()->after('duration');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employe_leave_entities') && Schema::hasColumn('employe_leave_entities', 'half_day_session')) {
            Schema::table('employe_leave_entities', function (Blueprint $table) {
                $table->dropColumn('half_day_session');
            });
        }

        if (Schema::hasTable('employe_leave_requests')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('employe_leave_requests', 'half_day_session') ? 'half_day_session' : null,
                    Schema::hasColumn('employe_leave_requests', 'is_half_day') ? 'is_half_day' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('leave_type_settings') && Schema::hasColumn('leave_type_settings', 'half_day_applicable')) {
            Schema::table('leave_type_settings', function (Blueprint $table) {
                $table->dropColumn('half_day_applicable');
            });
        }
    }
};
