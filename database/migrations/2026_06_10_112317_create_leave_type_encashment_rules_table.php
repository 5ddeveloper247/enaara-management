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
        Schema::create('leave_type_encashment_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->integer('service_months')->unsigned()->default(0);
            $table->foreignId('role_level_id')->constrained('role_levels')->restrictOnDelete();
            $table->decimal('max_forward_days', 8, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['leave_type_id', 'role_level_id', 'service_months'], 'lt_encash_rule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_type_encashment_rules');
    }
};
