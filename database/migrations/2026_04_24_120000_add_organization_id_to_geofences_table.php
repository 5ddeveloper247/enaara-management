<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            if (! Schema::hasColumn('geofences', 'organization_id')) {
                $table->foreignId('organization_id')
                    ->nullable()
                    ->after('type')
                    ->constrained('organizations')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('geofences', function (Blueprint $table) {
            if (Schema::hasColumn('geofences', 'organization_id')) {
                $table->dropConstrainedForeignId('organization_id');
            }
        });
    }
};
