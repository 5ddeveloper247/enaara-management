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
        Schema::create('holiday_sbus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('public_holiday_id');
            $table->unsignedBigInteger('sbu_id'); 
            $table->foreign('public_holiday_id')->references('id')->on('public_holidays')->onDelete('cascade');
            $table->foreign('sbu_id')->references('id')->on('sbus')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_sbus');
    }
};
