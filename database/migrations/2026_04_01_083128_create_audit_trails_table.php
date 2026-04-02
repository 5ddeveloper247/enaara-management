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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();

            $table->timestamp('action_at')->useCurrent();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('sbu_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();

            $table->string('module', 100)->nullable();
            $table->string('action', 100); 
            $table->string('action_category', 100); 
            $table->string('severity', 20)->default('info'); 

            $table->string('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 255)->nullable();

            $table->string('auditable_type')->nullable(); 
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->json('meta')->nullable(); 
            $table->json('context')->nullable(); 
            $table->timestamps();

            $table->index('action_at');
            $table->index('user_id');
            $table->index('employee_id');
            $table->index('organization_id');
            $table->index('sbu_id');
            $table->index('department_id');
            $table->index('action_category');
            $table->index('severity');
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
