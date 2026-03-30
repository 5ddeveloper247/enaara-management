<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('request_type', ['leave', 'overtime', 'regularization', 'shift']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('branch')->nullable();
            $table->json('approval_levels'); // e.g. [{"level":1,"role":"Supervisor"},{"level":2,"role":"HR Manager"}]
            $table->unsignedSmallInteger('sla_hours')->default(24);
            $table->string('escalate_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
