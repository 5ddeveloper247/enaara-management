<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'roster_display_middle_name')) {
                $table->boolean('roster_display_middle_name')->default(false)->after('middle_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'roster_display_middle_name')) {
                $table->dropColumn('roster_display_middle_name');
            }
        });
    }
};
