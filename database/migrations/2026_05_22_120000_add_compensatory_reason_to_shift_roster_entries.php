<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->string('compensatory_reason', 32)
                ->nullable()
                ->after('is_compensatory_earned');
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->dropColumn('compensatory_reason');
        });
    }
};
