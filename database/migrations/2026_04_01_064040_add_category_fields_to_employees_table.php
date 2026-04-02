<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'employment_category')) {
                $table->string('employment_category', 50)->nullable()->after('biometric_id');
            }
            if (!Schema::hasColumn('employees', 'intern_type')) {
                $table->string('intern_type', 50)->nullable()->after('employment_category');
            }
            if (!Schema::hasColumn('employees', 'intern_duration')) {
                $table->string('intern_duration', 100)->nullable()->after('intern_type');
            }
            if (!Schema::hasColumn('employees', 'contractual_type')) {
                $table->string('contractual_type', 50)->nullable()->after('intern_duration');
            }
            if (!Schema::hasColumn('employees', 'engagement_mode')) {
                $table->string('engagement_mode', 50)->nullable()->after('contractual_type');
            }
            if (!Schema::hasColumn('employees', 'hybrid_days')) {
                $table->json('hybrid_days')->nullable()->after('engagement_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            foreach (['hybrid_days', 'engagement_mode', 'contractual_type', 'intern_duration', 'intern_type', 'employment_category'] as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
