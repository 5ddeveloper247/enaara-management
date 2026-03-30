<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe_leave_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_type_id')
                ->nullable()
                ->constrained('leave_types')
                ->nullOnDelete();

            $table->foreignId('from_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('to_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('from_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('duration', 5, 2)->nullable();
            $table->text('reason')->nullable();

            $table->tinyInteger('action_type')
                ->default(0)
                ->comment('0: NULL, 1: recommended, 2: not_recommended');

            $table->tinyInteger('status')
                ->default(0)
                ->comment('0: in_approval, 1: recommended, 2: not_recommended, 3: approved, 4: rejected, 5: cancelled');

            $table->index('leave_type_id');
            $table->index('from_employee_id');
            $table->index('to_employee_id');
            $table->index('from_user_id');
            $table->index('to_user_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_leave_requests');
    }
};
