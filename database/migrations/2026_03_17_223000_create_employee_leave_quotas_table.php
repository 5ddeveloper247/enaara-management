<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_quotas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnDelete();

            // Quotas are usually yearly; keep it simple and explicit
            $table->unsignedSmallInteger('year');

            // Total allowed quota for that year (default comes from leave_types.annual_quota when you seed/create)
            $table->decimal('quota', 5, 2)->default(0);

            // How much has been taken/used in that year
            $table->decimal('used', 5, 2)->default(0);

            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
            $table->index(['employee_id', 'year']);
            $table->index(['leave_type_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_quotas');
    }
};

