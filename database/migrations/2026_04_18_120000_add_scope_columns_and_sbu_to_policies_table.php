<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            if (! Schema::hasColumn('policies', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('applicable_details');
            }
            if (! Schema::hasColumn('policies', 'sbu_id')) {
                $table->unsignedBigInteger('sbu_id')->nullable()->after('organization_id');
            }
            if (! Schema::hasColumn('policies', 'sbu_floor_id')) {
                $table->unsignedBigInteger('sbu_floor_id')->nullable()->after('sbu_id');
            }
        });

        DB::table('policies')->where('applicable_to', 'branch')->update(['applicable_to' => 'sbu']);

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE policies MODIFY COLUMN applicable_to ENUM('global','organization','sbu','floor') NOT NULL DEFAULT 'global'");
        }

        Schema::table('policies', function (Blueprint $table) {
            if (Schema::hasColumn('policies', 'organization_id')) {
                $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            }
            if (Schema::hasColumn('policies', 'sbu_id')) {
                $table->foreign('sbu_id')->references('id')->on('sbus')->nullOnDelete();
            }
            if (Schema::hasColumn('policies', 'sbu_floor_id')) {
                $table->foreign('sbu_floor_id')->references('id')->on('sbu_floors')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['sbu_id']);
            $table->dropForeign(['sbu_floor_id']);
        });

        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn(['organization_id', 'sbu_id', 'sbu_floor_id']);
        });

        DB::table('policies')->where('applicable_to', 'sbu')->update(['applicable_to' => 'branch']);

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE policies MODIFY COLUMN applicable_to ENUM('global','organization','branch','floor') NOT NULL DEFAULT 'global'");
        }
    }
};
