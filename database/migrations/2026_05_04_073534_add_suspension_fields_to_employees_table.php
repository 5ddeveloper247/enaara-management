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
        Schema::table('employees', function (Blueprint $table) {
            $table->text('suspension_reason')->nullable()->after('termination_date');
            $table->date('suspension_start_date')->nullable()->after('suspension_reason');
            $table->date('suspension_end_date')->nullable()->after('suspension_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['suspension_reason', 'suspension_start_date', 'suspension_end_date']);
        });
    }
};
