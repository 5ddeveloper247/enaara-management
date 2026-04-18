<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            if (! Schema::hasColumn('workflows', 'sbu_id')) {
                $table->foreignId('sbu_id')->nullable()->after('organization_id')->constrained('sbus')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropForeign(['sbu_id']);
            $table->dropColumn('sbu_id');
        });
    }
};
