<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('geofences')
            ->select('organization_id', 'sbu_id', 'name', DB::raw('COUNT(*) as total'))
            ->groupBy('organization_id', 'sbu_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new \RuntimeException('Cannot add unique index to geofences (organization_id, sbu_id, name) because duplicate site names already exist within the same organization and SBU.');
        }

        Schema::table('geofences', function (Blueprint $table) {
            $table->unique(['organization_id', 'sbu_id', 'name'], 'geofences_org_sbu_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            $table->dropUnique('geofences_org_sbu_name_unique');
        });
    }
};
