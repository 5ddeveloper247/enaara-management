<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $result = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();

        return (bool) $result;
    }

    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if ($this->indexExists('departments', 'departments_organization_id_name_unique')) {
                $table->dropUnique('departments_organization_id_name_unique');
            }
            if (! $this->indexExists('departments', 'departments_org_sbu_name_unique')) {
                $table->unique(['organization_id', 'sbu_id', 'name'], 'departments_org_sbu_name_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if ($this->indexExists('departments', 'departments_org_sbu_name_unique')) {
                $table->dropUnique('departments_org_sbu_name_unique');
            }
            if (! $this->indexExists('departments', 'departments_organization_id_name_unique')) {
                $table->unique(['organization_id', 'name'], 'departments_organization_id_name_unique');
            }
        });
    }
};
