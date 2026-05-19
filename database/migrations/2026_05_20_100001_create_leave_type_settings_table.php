<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_type_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->unique()->constrained('leave_types')->cascadeOnDelete();
            $table->string('employment_type', 32)->default('all');
            $table->string('gender', 16)->default('all');
            $table->unsignedSmallInteger('min_service_months')->default(0);
            $table->string('eligible_from', 32)->default('doj');
            $table->boolean('probation_eligible')->default(true);
            $table->string('unit_of_leave', 16)->default('days');
            $table->string('accrual_frequency', 32)->nullable();
            $table->unsignedTinyInteger('accrual_start_month')->nullable();
            $table->string('carry_forward', 32)->default('no');
            $table->decimal('max_carry_forward_days', 8, 2)->nullable();
            $table->string('encashment_allowed', 32)->default('no');
            $table->string('encashment_rule', 32)->nullable();
            $table->unsignedSmallInteger('max_consecutive_days')->nullable();
            $table->unsignedSmallInteger('advance_notice_days')->default(0);
            $table->boolean('short_leave_applicable')->default(false);
            $table->unsignedTinyInteger('short_leave_max_hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_type_settings');
    }
};
