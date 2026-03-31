<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_id_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20)->default('EMP');
            $table->unsignedInteger('last_number')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_id_sequences');
    }
};
