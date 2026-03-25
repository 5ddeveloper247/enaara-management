<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();

                // Personal Info
                $table->string('full_name')->nullable();
                $table->string('email')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->string('employee_code')->unique()->nullable();

                // Organization
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
                $table->string('employee_type')->nullable();
                $table->string('employment_type')->nullable();
                $table->string('site')->nullable();
                $table->date('join_date')->nullable();
                $table->boolean('floor_access')->default(false);
                $table->string('biometric_id')->nullable();
                $table->boolean('sync_with_biometric')->default(false);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('employees', function (Blueprint $table) {
                // Personal Info
                if (Schema::hasColumn('employees', 'name') && !Schema::hasColumn('employees', 'full_name')) {
                    $table->renameColumn('name', 'full_name');
                } elseif (!Schema::hasColumn('employees', 'full_name')) {
                    $table->string('full_name')->nullable()->after('id');
                }

                if (!Schema::hasColumn('employees', 'email')) {
                    $table->string('email')->unique()->nullable()->after('full_name');
                }

                if (Schema::hasColumn('employees', 'phone_number') && !Schema::hasColumn('employees', 'phone')) {
                    $table->renameColumn('phone_number', 'phone');
                } elseif (!Schema::hasColumn('employees', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }

                if (!Schema::hasColumn('employees', 'employee_code')) {
                    $table->string('employee_code')->unique()->nullable()->after('phone');
                }

                // Organization
                if (!Schema::hasColumn('employees', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('employee_code');
                }

                if (!Schema::hasColumn('employees', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('organization_id');
                }

                if (!Schema::hasColumn('employees', 'employee_type')) {
                    $table->string('employee_type')->nullable()->after('department_id');
                }

                if (!Schema::hasColumn('employees', 'employment_type')) {
                    $table->string('employment_type')->nullable()->after('employee_type');
                }

                if (!Schema::hasColumn('employees', 'site')) {
                    $table->string('site')->nullable()->after('employment_type');
                }

                if (!Schema::hasColumn('employees', 'join_date')) {
                    $table->date('join_date')->nullable()->after('site');
                }

                if (!Schema::hasColumn('employees', 'floor_access')) {
                    $table->boolean('floor_access')->default(false)->after('join_date');
                }

                if (!Schema::hasColumn('employees', 'biometric_id')) {
                    $table->string('biometric_id')->nullable()->after('floor_access');
                }

                if (!Schema::hasColumn('employees', 'sync_with_biometric')) {
                    $table->boolean('sync_with_biometric')->default(false)->after('biometric_id');
                }

                if (!Schema::hasColumn('employees', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('sync_with_biometric');
                }

                if (!Schema::hasColumn('employees', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('user_id');
                }

                if (!Schema::hasColumn('employees', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For a sync migration, we don't necessarily want to drop the whole table if it existed before.
        // But for consistency with the initial intention of 'create_employe_models_table', we keep it dropIfExists or empty.
        // Schema::dropIfExists('employees'); // Too dangerous if it's a sync.
    }

};
