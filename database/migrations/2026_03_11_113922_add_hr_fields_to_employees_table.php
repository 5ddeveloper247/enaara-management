<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('name')
                    ->constrained('organizations')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('organization_id')
                    ->constrained('departments')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('employees', 'employee_type_id')) {
                $table->foreignId('employee_type_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('employee_types')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('employees', 'email')) {
                $table->string('email')->nullable()->after('employee_type_id');
            }

            if (!Schema::hasColumn('employees', 'phone_number')) {
                $table->string('phone_number', 32)->nullable()->after('email');
            }

            if (!Schema::hasColumn('employees', 'cnic')) {
                $table->string('cnic', 32)->nullable()->after('phone_number');
            }

            if (!Schema::hasColumn('employees', 'gender')) {
                $table->string('gender', 16)->nullable()->after('cnic');
            }

            if (!Schema::hasColumn('employees', 'nationality')) {
                $table->string('nationality', 64)->nullable()->after('gender');
            }

            if (!Schema::hasColumn('employees', 'dob')) {
                $table->date('dob')->nullable()->after('nationality');
            }

            if (!Schema::hasColumn('employees', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_manager');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            }

            if (Schema::hasColumn('employees', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            if (Schema::hasColumn('employees', 'employee_type_id')) {
                $table->dropForeign(['employee_type_id']);
                $table->dropColumn('employee_type_id');
            }

            if (Schema::hasColumn('employees', 'email')) {
                $table->dropColumn('email');
            }

            if (Schema::hasColumn('employees', 'phone_number')) {
                $table->dropColumn('phone_number');
            }

            if (Schema::hasColumn('employees', 'cnic')) {
                $table->dropColumn('cnic');
            }

            if (Schema::hasColumn('employees', 'gender')) {
                $table->dropColumn('gender');
            }

            if (Schema::hasColumn('employees', 'nationality')) {
                $table->dropColumn('nationality');
            }

            if (Schema::hasColumn('employees', 'dob')) {
                $table->dropColumn('dob');
            }

            if (Schema::hasColumn('employees', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
