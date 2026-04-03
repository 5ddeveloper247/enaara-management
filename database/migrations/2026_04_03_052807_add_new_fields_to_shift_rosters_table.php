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
        Schema::table('shift_rosters', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('roster_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->time('check_in')->nullable()->after('end_time');
            $table->time('check_out')->nullable()->after('check_in');
            $table->string('floor')->nullable()->after('check_out');
            $table->boolean('late_check_in')->default(false)->after('floor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_rosters', function (Blueprint $table) {
            $table->dropColumn(['shift_type', 'start_time', 'end_time', 'check_in', 'check_out', 'floor', 'late_check_in']);
        });
    }
};
