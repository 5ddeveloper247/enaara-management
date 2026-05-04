<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            if (Schema::hasColumn('outsourced_employees', 'department_id')) {
                // Determine the foreign key name. Laravel usually follows [table]_[column]_foreign
                // But let's use a try-catch or just dropForeign by column name if supported.
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
            if (!Schema::hasColumn('outsourced_employees', 'service_type')) {
                $table->string('service_type', 150)->nullable()->after('sbu_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outsourced_employees', function (Blueprint $table) {
            if (Schema::hasColumn('outsourced_employees', 'service_type')) {
                $table->dropColumn('service_type');
            }
            if (!Schema::hasColumn('outsourced_employees', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('sbu_id')->constrained('departments')->nullOnDelete();
            }
        });
    }
};
