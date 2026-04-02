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
        Schema::table('public_holidays', function (Blueprint $table) {
            $table->enum('department_scope', ['all', 'specific', 'none'])->default('none')->after('organization_scope'); 
            $table->enum('sbu_scope', ['all', 'specific', 'none'])->default('none')->after('department_scope'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_holidays', function (Blueprint $table) {
            //
        });
    }
};
