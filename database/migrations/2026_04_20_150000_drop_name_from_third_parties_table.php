<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            if (Schema::hasColumn('third_parties', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            if (! Schema::hasColumn('third_parties', 'name')) {
                $table->string('name')->nullable()->after('organization_id');
            }
        });
    }
};
