<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('standard_schedule_mode', 20)->nullable()->after('hybrid_days');
            $table->json('working_days')->nullable()->after('standard_schedule_mode');
            $table->time('working_start_time')->nullable()->after('working_days');
            $table->time('working_end_time')->nullable()->after('working_start_time');
            $table->unsignedSmallInteger('opening_grace_period')->nullable()->after('working_end_time');
            $table->unsignedSmallInteger('closing_grace_period')->nullable()->after('opening_grace_period');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'standard_schedule_mode',
                'working_days',
                'working_start_time',
                'working_end_time',
                'opening_grace_period',
                'closing_grace_period',
            ]);
        });
    }
};
