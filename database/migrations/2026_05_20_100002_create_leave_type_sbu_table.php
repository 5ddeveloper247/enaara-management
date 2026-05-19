<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_type_sbu')) {
            Schema::create('leave_type_sbu', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->foreignId('sbu_id')->constrained('sbus')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['leave_type_id', 'sbu_id'], 'leave_type_sbu_unique');
            });
        }

        if (Schema::hasColumn('leave_types', 'sbu_id')) {
            $rows = DB::table('leave_types')->whereNotNull('sbu_id')->get(['id', 'sbu_id']);
            foreach ($rows as $row) {
                $exists = DB::table('leave_type_sbu')
                    ->where('leave_type_id', $row->id)
                    ->where('sbu_id', $row->sbu_id)
                    ->exists();
                if (! $exists) {
                    DB::table('leave_type_sbu')->insert([
                        'leave_type_id' => $row->id,
                        'sbu_id' => $row->sbu_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        try {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropUnique('leave_types_org_sbu_name_unique');
            });
        } catch (\Exception $e) {
        }

        try {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->unique(['organization_id', 'name'], 'leave_types_org_name_unique');
            });
        } catch (\Exception $e) {
        }
    }

    public function down(): void
    {
        try {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->dropUnique('leave_types_org_name_unique');
            });
        } catch (\Exception $e) {
        }

        try {
            Schema::table('leave_types', function (Blueprint $table) {
                $table->unique(['organization_id', 'sbu_id', 'name'], 'leave_types_org_sbu_name_unique');
            });
        } catch (\Exception $e) {
        }

        Schema::dropIfExists('leave_type_sbu');
    }
};
