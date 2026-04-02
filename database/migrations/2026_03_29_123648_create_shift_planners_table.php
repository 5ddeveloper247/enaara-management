<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_planners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->nullable()->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('clock_in_window_minutes')->default(0);
            $table->unsignedInteger('clock_out_window_minutes')->default(0);
            $table->unsignedInteger('shift_duration_minutes')->nullable();
            $table->unsignedInteger('grace_period_minutes')->default(0);
            $table->unsignedInteger('break_time_minutes')->default(0);
            $table->boolean('overtime_allowed')->default(true);
            $table->decimal('overtime_trigger_hours', 4, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_planners');
    }
};
