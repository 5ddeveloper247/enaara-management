<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'is_system_admin')) {
                $table->boolean('is_system_admin')->default(false)->after('is_primary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'is_system_admin')) {
                $table->dropColumn('is_system_admin');
            }
        });
    }
};
