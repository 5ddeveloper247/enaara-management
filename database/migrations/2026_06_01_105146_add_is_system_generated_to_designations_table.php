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
        if (! Schema::hasColumn('designations', 'is_system_generated')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->boolean('is_system_generated')->default(false)->after('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('designations', 'is_system_generated')) {
            Schema::table('designations', function (Blueprint $table) {
                $table->dropColumn('is_system_generated');
            });
        }
    }
};
