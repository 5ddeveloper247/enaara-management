<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_levels', function (Blueprint $table) {
            $table->unique('name', 'role_levels_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('role_levels', function (Blueprint $table) {
            $table->dropUnique('role_levels_name_unique');
        });
    }
};
