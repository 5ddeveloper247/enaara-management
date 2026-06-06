<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_roster_approval_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_roster_approval_request_id');
            $table->foreign('shift_roster_approval_request_id', 'sr_approval_segments_request_fk')
                ->references('id')
                ->on('shift_roster_approval_requests')
                ->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('approver_employee_id')->nullable();
            $table->foreign('approver_employee_id', 'sr_approval_segments_approver_fk')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
            $table->unsignedInteger('shift_count')->default(0);
            $table->unsignedInteger('off_day_count')->default(0);
            $table->unsignedInteger('employee_count')->default(0);
            $table->string('approval_status', 20)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(
                ['shift_roster_approval_request_id', 'approval_status'],
                'sr_approval_segments_request_status_idx'
            );
            $table->index(
                ['approver_employee_id', 'approval_status'],
                'sr_approval_segments_approver_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_roster_approval_segments');
    }
};
