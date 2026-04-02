<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employe_leave_requests', function (Blueprint $table) {
            $table->foreignId('department_id')->after('to_user_id')->nullable()->constrained('departments')->nullOnDelete();
        });

        Schema::table('employe_leave_entities', function (Blueprint $table) {
            $table->foreignId('department_id')->after('leave_type_id')->nullable()->constrained('departments')->nullOnDelete();
        });

        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->foreignId('department_id')->after('leave_type_id')->nullable()->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employe_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('employe_leave_entities', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
