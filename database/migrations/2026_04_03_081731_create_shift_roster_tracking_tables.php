<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Shift Roster Assignments Table (The "Request" or "Bulk" definition)
        Schema::create('shift_roster_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_planner_id')->constrained('shift_planners')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('days'); // Store repeat days: ["monday", "tuesday", ...]
            $table->string('assign_mode')->default('default'); // default or custom
            $table->boolean('check_conflicts')->default(true);
            $table->boolean('override_existing')->default(false);
            $table->boolean('exclude_weekends')->default(false);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 2. Pivot Table for Assignments and Employees
        Schema::create('shift_roster_assignment_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('shift_roster_assignments')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Create Shift Roster Entries Table (The Daily tracking records)
        Schema::create('shift_roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->nullable()->constrained('shift_roster_assignments')->onDelete('set null');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_planner_id')->constrained('shift_planners')->onDelete('cascade');
            $table->date('roster_date');
            
            // Cached timings and details from the shift at time of assignment
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('floor')->nullable();
            $table->boolean('late_check_in')->default(false);

            // Tracking Status
            $table->string('status')->default('pending'); // pending, used, absent, holiday, blackout
            $table->boolean('is_compensatory_earned')->default(false);
            
            $table->timestamps();

            $table->unique(['employee_id', 'roster_date', 'shift_planner_id'], 'unique_roster_entry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_roster_entries');
        Schema::dropIfExists('shift_roster_assignment_employee');
        Schema::dropIfExists('shift_roster_assignments');
    }
};
