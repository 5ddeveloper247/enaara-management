<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_roster_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_type', 20)->default('single');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('outsourced_employee_id')->nullable()->constrained('outsourced_employees')->nullOnDelete();
            $table->foreignId('approver_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('shift_count')->default(0);
            $table->unsignedInteger('off_day_count')->default(0);
            $table->string('shift_label')->nullable();
            $table->string('approval_status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['approval_status', 'approver_employee_id'], 'sr_approval_status_approver_idx');
            $table->index(['employee_id', 'approval_status'], 'sr_approval_employee_status_idx');
            $table->index(['outsourced_employee_id', 'approval_status'], 'sr_approval_outsourced_status_idx');
        });

        Schema::create('shift_roster_approval_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_roster_approval_request_id');
            $table->foreign('shift_roster_approval_request_id', 'sr_approval_items_request_fk')
                ->references('id')
                ->on('shift_roster_approval_requests')
                ->cascadeOnDelete();
            $table->date('roster_date');
            $table->string('entry_type', 20)->default('shift');
            $table->foreignId('shift_planner_id')->nullable()->constrained('shift_planners')->nullOnDelete();
            $table->boolean('is_custom_time')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('floor')->nullable();
            $table->text('location_text')->nullable();
            $table->text('notes')->nullable();
            $table->string('entry_status', 20)->default('pending');
            $table->timestamps();

            $table->index(['shift_roster_approval_request_id', 'roster_date'], 'sr_approval_items_request_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_roster_approval_request_items');
        Schema::dropIfExists('shift_roster_approval_requests');
    }
};
