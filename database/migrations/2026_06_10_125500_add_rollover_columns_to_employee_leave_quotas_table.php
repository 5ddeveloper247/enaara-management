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
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->decimal('carried_forward', 8, 2)->default(0)->after('used');
            $table->decimal('encashed', 8, 2)->default(0)->after('carried_forward');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->dropColumn(['carried_forward', 'encashed']);
        });
    }
};
