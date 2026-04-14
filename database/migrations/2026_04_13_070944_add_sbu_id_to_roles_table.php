<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('sbu_id')->nullable()->after('organization_id');

            $table->foreign('sbu_id')
                ->references('id')
                ->on('sbus')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['sbu_id']);
            $table->dropColumn('sbu_id');
        });
    }
};
