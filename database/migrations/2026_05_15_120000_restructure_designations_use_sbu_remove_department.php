<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('designations')) {
            return;
        }

        if (! Schema::hasColumn('designations', 'department_id')) {
            if (! Schema::hasColumn('designations', 'sbu_id')) {
                Schema::table('designations', function (Blueprint $table): void {
                    $table->foreignId('sbu_id')->constrained('sbus')->cascadeOnDelete();
                    $table->unique(['sbu_id', 'name']);
                });
            }

            return;
        }

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropForeign(['department_id']);
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropUnique(['department_id', 'name']);
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->unsignedBigInteger('sbu_id')->nullable()->after('id');
        });

        $rows = DB::table('designations')->select(['id', 'department_id'])->whereNotNull('department_id')->get();
        foreach ($rows as $row) {
            $sbuId = DB::table('departments')->where('id', $row->department_id)->value('sbu_id');
            if ($sbuId) {
                DB::table('designations')->where('id', $row->id)->update(['sbu_id' => $sbuId]);
            }
        }

        if (DB::table('designations')->whereNull('sbu_id')->exists()) {
            throw new \RuntimeException('designations migration failed: could not resolve sbu_id for all rows.');
        }

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropColumn('department_id');
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->foreign('sbu_id')->references('id')->on('sbus')->cascadeOnDelete();
            $table->unique(['sbu_id', 'name']);
        });
    }

    public function down(): void
    {
        throw new \RuntimeException('designations sbu migration cannot be reversed safely.');
    }
};
