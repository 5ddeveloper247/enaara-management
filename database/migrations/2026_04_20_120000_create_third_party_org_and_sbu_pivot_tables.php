<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('third_party_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['third_party_id', 'organization_id'], 'tp_org_unique');
        });

        Schema::create('third_party_sbu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();
            $table->foreignId('sbu_id')->constrained('sbus')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['third_party_id', 'sbu_id'], 'tp_sbu_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('third_party_sbu');
        Schema::dropIfExists('third_party_organizations');
    }
};
