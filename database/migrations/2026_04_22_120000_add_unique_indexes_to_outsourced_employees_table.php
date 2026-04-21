<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            $table->unique('cnic_number', 'outsourced_employees_cnic_unique');
            $table->unique('biometric_id', 'outsourced_employees_biometric_unique');
        });
    }

    public function down(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            $table->dropUnique('outsourced_employees_cnic_unique');
            $table->dropUnique('outsourced_employees_biometric_unique');
        });
    }
};

