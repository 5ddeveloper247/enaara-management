<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_medicals', function (Blueprint $table) {
            $table->date('last_fitness_test_date')->nullable()->after('last_fitness_test');
            $table->string('last_fitness_test_result', 20)->nullable()->after('last_fitness_test_date');
        });
    }

    public function down(): void
    {
        Schema::table('employee_medicals', function (Blueprint $table) {
            $table->dropColumn(['last_fitness_test_date', 'last_fitness_test_result']);
        });
    }
};
