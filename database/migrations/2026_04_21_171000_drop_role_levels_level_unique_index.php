<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    public function up(): void
    {
        Schema::table('role_levels', function (Blueprint $table) {
            if ($this->indexExists('role_levels', 'role_levels_level_unique')) {
                $table->dropUnique('role_levels_level_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('role_levels', function (Blueprint $table) {
            if (! $this->indexExists('role_levels', 'role_levels_level_unique')) {
                $table->unique('level', 'role_levels_level_unique');
            }
        });
    }
};
