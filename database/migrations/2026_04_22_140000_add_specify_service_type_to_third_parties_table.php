<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->string('specify_service_type', 150)->nullable()->after('service_type');
        });
    }

    public function down(): void
    {
        Schema::table('third_parties', function (Blueprint $table) {
            $table->dropColumn('specify_service_type');
        });
    }
};
