<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Police Verifications
        Schema::create('employee_police_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('verification_status', 30)->nullable();
            $table->string('msr_letter_no')->nullable();
            $table->string('addressee')->nullable();
            $table->string('verifying_authority')->nullable();
            $table->string('verification_letter_no')->nullable();
            $table->date('next_verification_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Armed Forces
        Schema::create('employee_armed_forces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('service_no')->nullable();
            $table->string('rank')->nullable();
            $table->string('medical_category')->nullable();
            $table->date('date_of_commissioning')->nullable();
            $table->date('date_of_retirement')->nullable();
            $table->string('reason_of_retirement')->nullable();
            $table->string('corps_regiment')->nullable();
            $table->string('ex_army_unit')->nullable();
            $table->string('trade')->nullable();
            $table->string('pma_lc_ots')->nullable();
            $table->timestamps();
        });

        // Contacts
        Schema::create('employee_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('residence_phone', 20)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->string('cell_no', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->timestamps();
        });

        // Bank Details
        Schema::create('employee_bank_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('account_title')->nullable();
            $table->string('account_no')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_type', 20)->nullable();
            $table->timestamps();
        });

        // Family Members
        Schema::create('employee_family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('gender', 10)->nullable();
            $table->date('dob')->nullable();
            $table->string('relation')->nullable();
            $table->string('occupation')->nullable();
            $table->timestamps();
        });

        // Academics
        Schema::create('employee_academics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('degree');
            $table->string('grade_cgpa', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('institute')->nullable();
            $table->timestamps();
        });

        // Ex-Employments
        Schema::create('employee_ex_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('organization');
            $table->string('designation')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('salary')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->timestamps();
        });

        // Medicals
        Schema::create('employee_medicals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->text('last_fitness_test')->nullable();
            $table->string('has_disability', 5)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('disability_type')->nullable();
            $table->text('disability_description')->nullable();
            $table->timestamps();
        });

        // References
        Schema::create('employee_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->tinyInteger('ref_number')->default(1);
            $table->string('name')->nullable();
            $table->string('designation')->nullable();
            $table->string('organization')->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_references');
        Schema::dropIfExists('employee_medicals');
        Schema::dropIfExists('employee_ex_employments');
        Schema::dropIfExists('employee_academics');
        Schema::dropIfExists('employee_family_members');
        Schema::dropIfExists('employee_bank_details');
        Schema::dropIfExists('employee_contacts');
        Schema::dropIfExists('employee_armed_forces');
        Schema::dropIfExists('employee_police_verifications');
    }
};
