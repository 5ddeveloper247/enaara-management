<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('designations')) {
            return;
        }

        if (! Schema::hasColumn('designations', 'department_id')) {
            Schema::table('designations', function (Blueprint $table): void {
                $table->foreignId('department_id')->nullable()->after('sbu_id')->constrained('departments')->cascadeOnDelete();
            });
        }

        try {
            Schema::table('designations', function (Blueprint $table): void {
                $table->dropUnique(['sbu_id', 'name']);
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('designations', function (Blueprint $table): void {
                $table->unique(['department_id', 'name']);
            });
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('designations') || ! Schema::hasColumn('designations', 'department_id')) {
            return;
        }

        try {
            Schema::table('designations', function (Blueprint $table): void {
                $table->dropUnique(['department_id', 'name']);
            });
        } catch (\Throwable) {
        }

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropForeign(['department_id']);
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropColumn('department_id');
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->unique(['sbu_id', 'name']);
        });
    }
};
