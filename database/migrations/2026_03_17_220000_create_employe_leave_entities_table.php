<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employe_leave_entities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_request_id')
                ->nullable()
                ->constrained('employe_leave_requests')
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('leave_type_id')
                ->nullable()
                ->constrained('leave_types')
                ->nullOnDelete();

            $table->date('leave_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('duration', 5, 2)->nullable();
            $table->tinyInteger('status')
                ->default(0)
                ->comment('0: pending, 1: taken');

            $table->timestamps();

            $table->unique(['leave_request_id', 'leave_date']);
            $table->index(['employee_id', 'leave_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employe_leave_entities');
    }
};

