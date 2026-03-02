<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sbu_floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sbu_id')->constrained('sbus')->cascadeOnDelete();
            $table->string('name');
            $table->integer('floor_number')->nullable();
            $table->enum('floor_type', ['corporate', 'operational', 'mixed'])->default('operational');
            $table->boolean('is_restricted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sbu_floors');
    }
};
