<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outsourced_employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('cnic_number', 20);
            $table->string('mobile_number', 20);
            $table->string('photo_path')->nullable();

            $table->string('contractor_company_name', 150);
            $table->string('supervisor_name', 120);
            $table->string('supervisor_contact_number', 20);

            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('job_role_trade', 150);
            $table->string('placement_floor', 120);
            $table->date('date_of_deployment');

            $table->string('biometric_id', 60)->nullable();
            $table->boolean('attendance_access')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['contractor_company_name']);
            $table->index(['department_id']);
            $table->index(['date_of_deployment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outsourced_employees');
    }
};

