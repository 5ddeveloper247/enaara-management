<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'termination_reason')) {
                $table->text('termination_reason')->nullable();
            }
            if (! Schema::hasColumn('employees', 'termination_date')) {
                $table->date('termination_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'termination_date')) {
                $table->dropColumn('termination_date');
            }
            if (Schema::hasColumn('employees', 'termination_reason')) {
                $table->dropColumn('termination_reason');
            }
        });
    }
};
