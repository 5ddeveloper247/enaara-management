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
        Schema::create('outsourced_employee_floor_privileges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outsourced_employee_id');
            $table->unsignedBigInteger('sbu_floor_id');
            
            $table->foreign('outsourced_employee_id', 'fk_oe_floor_privs_oe_id')->references('id')->on('outsourced_employees')->onDelete('cascade');
            $table->foreign('sbu_floor_id', 'fk_oe_floor_privs_sbu_floor_id')->references('id')->on('sbu_floors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outsourced_employee_floor_privileges');
    }
};
