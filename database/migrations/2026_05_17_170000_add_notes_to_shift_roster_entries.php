<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_roster_entries', 'notes')) {
                $table->text('notes')->nullable()->after('floor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (Schema::hasColumn('shift_roster_entries', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
