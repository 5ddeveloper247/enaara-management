<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employe_leave_requests')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('employe_leave_requests', 'is_outstation_leave')) {
                    $table->boolean('is_outstation_leave')->default(false)->after('half_day_session');
                }
                if (! Schema::hasColumn('employe_leave_requests', 'outstation_destination')) {
                    $table->string('outstation_destination', 20)->nullable()->after('is_outstation_leave');
                }
                if (! Schema::hasColumn('employe_leave_requests', 'exempt_days')) {
                    $table->decimal('exempt_days', 5, 2)->default(0)->after('outstation_destination');
                }
            });
        }

        if (Schema::hasTable('employe_leave_entities')) {
            Schema::table('employe_leave_entities', function (Blueprint $table) {
                if (! Schema::hasColumn('employe_leave_entities', 'counts_against_quota')) {
                    $table->boolean('counts_against_quota')->default(true)->after('duration');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employe_leave_entities') && Schema::hasColumn('employe_leave_entities', 'counts_against_quota')) {
            Schema::table('employe_leave_entities', function (Blueprint $table) {
                $table->dropColumn('counts_against_quota');
            });
        }

        if (Schema::hasTable('employe_leave_requests')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                $columns = array_filter([
                    Schema::hasColumn('employe_leave_requests', 'exempt_days') ? 'exempt_days' : null,
                    Schema::hasColumn('employe_leave_requests', 'outstation_destination') ? 'outstation_destination' : null,
                    Schema::hasColumn('employe_leave_requests', 'is_outstation_leave') ? 'is_outstation_leave' : null,
                ]);

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
