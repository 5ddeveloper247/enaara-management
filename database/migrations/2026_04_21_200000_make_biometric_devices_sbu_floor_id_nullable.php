<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->dropForeign(['sbu_floor_id']);
        });

        DB::statement('ALTER TABLE biometric_devices MODIFY sbu_floor_id BIGINT UNSIGNED NULL');

        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->foreign('sbu_floor_id')
                ->references('id')
                ->on('sbu_floors')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $nullRows = DB::table('biometric_devices')->whereNull('sbu_floor_id')->get(['id', 'sbu_id']);

        foreach ($nullRows as $row) {
            $floorId = DB::table('sbu_floors')
                ->where('sbu_id', $row->sbu_id)
                ->orderBy('id')
                ->value('id');

            if ($floorId) {
                DB::table('biometric_devices')->where('id', $row->id)->update(['sbu_floor_id' => $floorId]);
            }
        }

        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->dropForeign(['sbu_floor_id']);
        });

        DB::statement('ALTER TABLE biometric_devices MODIFY sbu_floor_id BIGINT UNSIGNED NOT NULL');

        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->foreign('sbu_floor_id')
                ->references('id')
                ->on('sbu_floors')
                ->restrictOnDelete();
        });
    }
};
