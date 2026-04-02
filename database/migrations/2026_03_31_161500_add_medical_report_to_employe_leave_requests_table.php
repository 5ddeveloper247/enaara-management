<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('employe_leave_requests', 'medical_report')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                $table->string('medical_report')->nullable()->after('reason');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employe_leave_requests', 'medical_report')) {
            Schema::table('employe_leave_requests', function (Blueprint $table) {
                $table->dropColumn('medical_report');
            });
        }
    }
};
