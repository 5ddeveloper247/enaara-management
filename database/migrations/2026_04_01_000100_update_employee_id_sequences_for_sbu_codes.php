<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_id_sequences', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_id_sequences', 'sbu_id')) {
                $table->unsignedBigInteger('sbu_id')->nullable()->after('id');
                $table->index('sbu_id');
            }
        });

        // Remove legacy global sequence rows with no SBU scope
        DB::table('employee_id_sequences')->whereNull('sbu_id')->delete();

        Schema::table('employee_id_sequences', function (Blueprint $table) {
            $table->unique('sbu_id', 'employee_id_sequences_sbu_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employee_id_sequences', function (Blueprint $table) {
            if (Schema::hasColumn('employee_id_sequences', 'sbu_id')) {
                $table->dropUnique('employee_id_sequences_sbu_id_unique');
                $table->dropIndex(['sbu_id']);
                $table->dropColumn('sbu_id');
            }
        });
    }
};
