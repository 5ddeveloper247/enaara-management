<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('employees', 'full_name'))         $table->string('full_name')->nullable()->after('id');
            if (!Schema::hasColumn('employees', 'father_name'))       $table->string('father_name')->nullable();
            if (!Schema::hasColumn('employees', 'employee_code'))     $table->string('employee_code')->nullable()->unique();
            if (!Schema::hasColumn('employees', 'email'))             $table->string('email')->nullable();
            if (!Schema::hasColumn('employees', 'phone'))             $table->string('phone', 32)->nullable();
            if (!Schema::hasColumn('employees', 'cnic'))              $table->string('cnic', 32)->nullable();
            if (!Schema::hasColumn('employees', 'cnic_expiry'))       $table->date('cnic_expiry')->nullable();
            if (!Schema::hasColumn('employees', 'father_cnic'))       $table->string('father_cnic', 32)->nullable();
            if (!Schema::hasColumn('employees', 'ntn'))               $table->string('ntn', 50)->nullable();
            if (!Schema::hasColumn('employees', 'gender'))            $table->string('gender', 16)->nullable();
            if (!Schema::hasColumn('employees', 'nationality'))       $table->string('nationality', 64)->nullable();
            if (!Schema::hasColumn('employees', 'dob'))               $table->date('dob')->nullable();
            if (!Schema::hasColumn('employees', 'domicile_district')) $table->string('domicile_district')->nullable();
            if (!Schema::hasColumn('employees', 'domicile_province')) $table->string('domicile_province')->nullable();
            if (!Schema::hasColumn('employees', 'city_of_birth'))     $table->string('city_of_birth')->nullable();
            if (!Schema::hasColumn('employees', 'religion'))          $table->string('religion')->nullable();
            if (!Schema::hasColumn('employees', 'sect'))              $table->string('sect')->nullable();
            if (!Schema::hasColumn('employees', 'marital_status'))    $table->string('marital_status', 20)->nullable();
            if (!Schema::hasColumn('employees', 'spouse_name'))       $table->string('spouse_name')->nullable();
            if (!Schema::hasColumn('employees', 'nok_name'))          $table->string('nok_name')->nullable();
            if (!Schema::hasColumn('employees', 'nok_cnic'))          $table->string('nok_cnic', 32)->nullable();
            if (!Schema::hasColumn('employees', 'nok_relation'))      $table->string('nok_relation')->nullable();
            if (!Schema::hasColumn('employees', 'nok_dob'))           $table->date('nok_dob')->nullable();
            if (!Schema::hasColumn('employees', 'nok_contact'))       $table->string('nok_contact', 20)->nullable();
            if (!Schema::hasColumn('employees', 'designation'))       $table->string('designation')->nullable();
            if (!Schema::hasColumn('employees', 'grade'))             $table->string('grade', 50)->nullable();
            if (!Schema::hasColumn('employees', 'branch'))            $table->string('branch')->nullable();
            if (!Schema::hasColumn('employees', 'location'))          $table->string('location')->nullable();
            if (!Schema::hasColumn('employees', 'employee_type'))     $table->string('employee_type')->nullable();
            if (!Schema::hasColumn('employees', 'employment_type'))   $table->string('employment_type')->nullable();
            if (!Schema::hasColumn('employees', 'employee_type_id'))  $table->unsignedBigInteger('employee_type_id')->nullable();
            if (!Schema::hasColumn('employees', 'organization_id'))   $table->unsignedBigInteger('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            if (!Schema::hasColumn('employees', 'sbu_id'))            $table->unsignedBigInteger('sbu_id')->nullable()->constrained('sbus')->nullOnDelete();
            if (!Schema::hasColumn('employees', 'department_id'))     $table->unsignedBigInteger('department_id')->nullable()->constrained('departments')->nullOnDelete();
            if (!Schema::hasColumn('employees', 'role_id'))           $table->unsignedBigInteger('role_id')->nullable();
            if (!Schema::hasColumn('employees', 'line_manager_id'))   $table->unsignedBigInteger('line_manager_id')->nullable();
            if (!Schema::hasColumn('employees', 'is_manager'))        $table->boolean('is_manager')->default(false);
            if (!Schema::hasColumn('employees', 'is_active'))         $table->boolean('is_active')->default(true);
            if (!Schema::hasColumn('employees', 'site'))              $table->string('site')->nullable();
            if (!Schema::hasColumn('employees', 'join_date'))         $table->date('join_date')->nullable();
            if (!Schema::hasColumn('employees', 'floor_access'))      $table->boolean('floor_access')->default(false);
            if (!Schema::hasColumn('employees', 'biometric_id'))      $table->string('biometric_id')->nullable();
            if (!Schema::hasColumn('employees', 'sync_with_biometric'))$table->boolean('sync_with_biometric')->default(false);
            if (!Schema::hasColumn('employees', 'user_id'))           $table->unsignedBigInteger('user_id')->nullable();
            if (!Schema::hasColumn('employees', 'deleted_at'))        $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'full_name','father_name','employee_code','email','phone','cnic','cnic_expiry',
                'father_cnic','ntn','gender','nationality','dob','domicile_district','domicile_province',
                'city_of_birth','religion','sect','marital_status','spouse_name','nok_name','nok_cnic',
                'nok_relation','nok_dob','nok_contact','designation','grade','branch','location',
                'employee_type','employment_type','employee_type_id','organization_id','sbu_id',
                'department_id','role_id','line_manager_id','is_manager','is_active','site',
                'join_date','floor_access','biometric_id','sync_with_biometric','user_id','deleted_at',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
