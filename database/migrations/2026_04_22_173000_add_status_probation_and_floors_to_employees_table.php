<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'employee_status')) {
                $table->string('employee_status', 20)->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('employees', 'probation_start_date')) {
                $table->date('probation_start_date')->nullable()->after('contract_end_date');
            }
            if (! Schema::hasColumn('employees', 'probation_end_date')) {
                $table->date('probation_end_date')->nullable()->after('probation_start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'probation_end_date')) {
                $table->dropColumn('probation_end_date');
            }
            if (Schema::hasColumn('employees', 'probation_start_date')) {
                $table->dropColumn('probation_start_date');
            }
            if (Schema::hasColumn('employees', 'employee_status')) {
                $table->dropColumn('employee_status');
            }
        });
    }
};
