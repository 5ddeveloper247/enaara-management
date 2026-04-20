<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sbu_floors', function (Blueprint $table) {
            $table->unique(['sbu_id', 'name'], 'sbu_floors_sbu_id_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sbu_floors', function (Blueprint $table) {
            $table->dropUnique('sbu_floors_sbu_id_name_unique');
        });
    }
};
