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
        Schema::table('employee_academics', function (Blueprint $table) {
            $table->string('degree_title')->nullable()->after('degree');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_academics', function (Blueprint $table) {
            $table->dropColumn('degree_title');
        });
    }
};
