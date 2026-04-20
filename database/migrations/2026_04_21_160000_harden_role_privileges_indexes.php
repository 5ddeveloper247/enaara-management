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

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    public function up(): void
    {
        DB::table('role_privileges')
            ->whereNull('role_id')
            ->orWhereNull('module_id')
            ->delete();

        DB::statement('DELETE rp FROM role_privileges rp LEFT JOIN roles r ON r.id = rp.role_id WHERE r.id IS NULL');
        DB::statement('DELETE rp FROM role_privileges rp LEFT JOIN modules m ON m.id = rp.module_id WHERE m.id IS NULL');
        DB::statement('DELETE rp1 FROM role_privileges rp1 INNER JOIN role_privileges rp2 ON rp1.role_id = rp2.role_id AND rp1.module_id = rp2.module_id AND rp1.id > rp2.id');

        Schema::table('role_privileges', function (Blueprint $table) {
            if (! $this->indexExists('role_privileges', 'role_privileges_role_id_index')) {
                $table->index('role_id', 'role_privileges_role_id_index');
            }
            if (! $this->indexExists('role_privileges', 'role_privileges_module_id_index')) {
                $table->index('module_id', 'role_privileges_module_id_index');
            }
            if (! $this->indexExists('role_privileges', 'role_privileges_role_module_unique')) {
                $table->unique(['role_id', 'module_id'], 'role_privileges_role_module_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('role_privileges', function (Blueprint $table) {
            if ($this->indexExists('role_privileges', 'role_privileges_role_module_unique')) {
                $table->dropUnique('role_privileges_role_module_unique');
            }
            if ($this->indexExists('role_privileges', 'role_privileges_role_id_index')) {
                $table->dropIndex('role_privileges_role_id_index');
            }
            if ($this->indexExists('role_privileges', 'role_privileges_module_id_index')) {
                $table->dropIndex('role_privileges_module_id_index');
            }
        });
    }
};
