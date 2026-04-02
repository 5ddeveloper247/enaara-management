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
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(100);
            $table->string('radius_unit')->default('meters');
            $table->enum('type', ['hard-lock', 'soft-lock'])->default('hard-lock');
            $table->foreignId('sbu_id')->nullable()->constrained('sbus')->onDelete('cascade');
            $table->boolean('anti_spoofing')->default(false);
            $table->boolean('offline_sync')->default(true);
            $table->boolean('auto_check_in')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
};
