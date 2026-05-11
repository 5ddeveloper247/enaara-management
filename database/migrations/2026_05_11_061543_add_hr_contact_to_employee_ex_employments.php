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
        Schema::table('employee_ex_employments', function (Blueprint $table) {
            $table->string('hr_contact')->nullable()->after('reason_for_leaving');
            $table->string('hr_email')->nullable()->after('hr_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_ex_employments', function (Blueprint $table) {
            $table->dropColumn(['hr_contact', 'hr_email']);
        });
    }
};
