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
        Schema::table('employee_police_verifications', function (Blueprint $table) {
            $table->date('msr_date')->nullable()->after('msr_letter_no');
            $table->date('verification_letter_date')->nullable()->after('verification_letter_no');
        });
    }

    public function down(): void
    {
        Schema::table('employee_police_verifications', function (Blueprint $table) {
            $table->dropColumn(['msr_date', 'verification_letter_date']);
        });
    }
};
