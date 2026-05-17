<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_roster_entries', 'location_text')) {
                $table->string('location_text', 15)->nullable()->after('floor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (Schema::hasColumn('shift_roster_entries', 'location_text')) {
                $table->dropColumn('location_text');
            }
        });
    }
};
