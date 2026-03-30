<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('category', [
                'Leave Policy',
                'Attendance Grace Period',
                'Geofencing Rules',
                'Shift Rota Protocols',
                'Security Policy',
                'HR Policy'
            ]);
            $table->enum('status', ['active', 'draft', 'archived'])->default('draft');
            $table->date('effective_date');
            $table->enum('applicable_to', ['global', 'organization', 'branch', 'floor'])->default('global');
            $table->string('applicable_details')->nullable();
            $table->text('description')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
