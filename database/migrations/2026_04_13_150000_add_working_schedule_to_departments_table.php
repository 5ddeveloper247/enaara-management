<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->json('working_days')->nullable()->after('description');
            $table->time('working_start_time')->nullable()->after('working_days');
            $table->time('working_end_time')->nullable()->after('working_start_time');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'working_start_time', 'working_end_time']);
        });
    }
};
