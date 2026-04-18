<?php

use App\Models\Role;
use App\Models\RoleLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Existing roles were created with level_id in the form but role_level_id was never persisted.
     * Link each role to role_levels by matching name (case-insensitive) so validations and FKs work.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'role_level_id')) {
            return;
        }

        $hasActive = Schema::hasColumn('role_levels', 'is_active');

        Role::query()
            ->whereNull('role_level_id')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($roles) use ($hasActive): void {
                foreach ($roles as $role) {
                    $q = RoleLevel::query()
                        ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower(trim((string) $role->name))]);
                    if ($hasActive) {
                        $q->where('is_active', true);
                    }
                    $levelId = $q->orderBy('id')->value('id');
                    if ($levelId) {
                        $role->update(['role_level_id' => $levelId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Non-destructive: do not clear role_level_id on rollback.
    }
};
