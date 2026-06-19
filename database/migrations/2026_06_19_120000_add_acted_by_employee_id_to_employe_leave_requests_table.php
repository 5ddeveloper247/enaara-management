<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employe_leave_requests')) {
            return;
        }

        Schema::table('employe_leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('employe_leave_requests', 'acted_by_employee_id')) {
                $table->foreignId('acted_by_employee_id')
                    ->nullable()
                    ->after('to_user_id')
                    ->constrained('employees')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employe_leave_requests')) {
            return;
        }

        Schema::table('employe_leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('employe_leave_requests', 'acted_by_employee_id')) {
                $table->dropConstrainedForeignId('acted_by_employee_id');
            }
        });
    }
};
