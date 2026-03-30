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
        Schema::create('holiday_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('public_holiday_id');
            $table->unsignedBigInteger('department_id'); 
            $table->foreign('public_holiday_id')->references('id')->on('public_holidays')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_departments');
    }
};
