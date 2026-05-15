<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        if (! Schema::hasColumn('employees', 'designation_id')) {
            Schema::table('employees', function (Blueprint $table): void {
                $table->foreignId('designation_id')->nullable()->after('designation')->constrained('designations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasColumn('employees', 'designation_id')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['designation_id']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn('designation_id');
        });
    }
};
