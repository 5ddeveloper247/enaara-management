<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignId('sbu_id')->constrained('sbus')->restrictOnDelete();
            $table->foreignId('sbu_floor_id')->constrained('sbu_floors')->restrictOnDelete();
            $table->string('device_name');
            $table->string('serial_number')->unique();
            $table->string('device_type');
            $table->string('brand_model');
            $table->string('ip_address', 45);
            $table->unsignedSmallInteger('port');
            $table->string('connection_type', 16);
            $table->string('device_status', 16);
            $table->string('online_status', 16)->default('unknown');
            $table->timestamp('last_sync_time')->nullable();
            $table->date('installation_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
