<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('supervisor_contact_number')->constrained('organizations')->nullOnDelete();
            $table->foreignId('sbu_id')->nullable()->after('organization_id')->constrained('sbus')->nullOnDelete();
            $table->index(['organization_id']);
            $table->index(['sbu_id']);
        });
    }

    public function down(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sbu_id');
            $table->dropConstrainedForeignId('organization_id');
        });
    }
};

