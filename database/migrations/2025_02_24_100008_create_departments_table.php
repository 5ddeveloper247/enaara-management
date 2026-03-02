<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sbu_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 64)->nullable();
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
