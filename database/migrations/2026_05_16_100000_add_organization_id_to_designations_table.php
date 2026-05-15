<?php

use App\Models\Designation;
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

        if (! Schema::hasColumn('designations', 'organization_id')) {
            Schema::table('designations', function (Blueprint $table): void {
                $table->foreignId('organization_id')->nullable()->after('id')->constrained('organizations');
            });
        }

        foreach (Designation::query()->with(['sbu:id,organization_id'])->cursor() as $designation) {
            $orgId = $designation->sbu?->organization_id;
            if ($orgId) {
                $designation->organization_id = (int) $orgId;
                $designation->saveQuietly();
            }
        }

        if (Designation::query()->whereNull('organization_id')->exists()) {
            throw new \RuntimeException('designations migration failed: could not resolve organization_id for all rows.');
        }

        Schema::table('designations', function (Blueprint $table): void {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('designations') || ! Schema::hasColumn('designations', 'organization_id')) {
            return;
        }

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('designations', function (Blueprint $table): void {
            $table->dropColumn('organization_id');
        });
    }
};
