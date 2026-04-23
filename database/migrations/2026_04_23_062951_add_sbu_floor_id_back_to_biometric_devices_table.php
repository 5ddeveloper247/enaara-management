<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('biometric_devices', 'sbu_floor_id')) {
                $table->unsignedBigInteger('sbu_floor_id')->nullable()->after('sbu_id');
                $table->foreign('sbu_floor_id')->references('id')->on('sbu_floors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('biometric_devices', function (Blueprint $table) {
            if (Schema::hasColumn('biometric_devices', 'sbu_floor_id')) {
                $table->dropForeign(['sbu_floor_id']);
                $table->dropColumn('sbu_floor_id');
            }
        });
    }
};
