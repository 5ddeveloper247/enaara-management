<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedSmallInteger('opening_grace_period')->nullable()->after('working_end_time');
            $table->unsignedSmallInteger('closing_grace_period')->nullable()->after('opening_grace_period');
        });

        Schema::table('sbus', function (Blueprint $table) {
            $table->unsignedSmallInteger('opening_grace_period')->nullable()->after('working_end_time');
            $table->unsignedSmallInteger('closing_grace_period')->nullable()->after('opening_grace_period');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedSmallInteger('opening_grace_period')->nullable()->after('working_end_time');
            $table->unsignedSmallInteger('closing_grace_period')->nullable()->after('opening_grace_period');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['opening_grace_period', 'closing_grace_period']);
        });

        Schema::table('sbus', function (Blueprint $table) {
            $table->dropColumn(['opening_grace_period', 'closing_grace_period']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['opening_grace_period', 'closing_grace_period']);
        });
    }
};
