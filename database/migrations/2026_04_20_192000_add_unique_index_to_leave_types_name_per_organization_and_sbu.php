<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('leave_types')
            ->select('organization_id', 'sbu_id', 'name', DB::raw('COUNT(*) as total'))
            ->groupBy('organization_id', 'sbu_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Cannot add unique index to leave_types (organization_id, sbu_id, name) because duplicate leave type names already exist within same organization and SBU.');
        }

        Schema::table('leave_types', function (Blueprint $table) {
            $table->unique(['organization_id', 'sbu_id', 'name'], 'leave_types_org_sbu_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropUnique('leave_types_org_sbu_name_unique');
        });
    }
};
