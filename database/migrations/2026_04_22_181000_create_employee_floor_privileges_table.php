<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_floor_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('sbu_floor_id')->constrained('sbu_floors')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'sbu_floor_id'], 'employee_floor_privileges_employee_floor_unique');
            $table->index('employee_id', 'employee_floor_privileges_employee_idx');
            $table->index('sbu_floor_id', 'employee_floor_privileges_floor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_floor_privileges');
    }
};
