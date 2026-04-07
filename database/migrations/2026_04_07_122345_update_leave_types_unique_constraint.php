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
        Schema::table('leave_types', function (Blueprint $table) {
            // Add new unique constraint INCLUDING department_id FIRST
            // This will satisfy the foreign key requirement for organization_id
            $table->unique(['organization_id', 'department_id', 'code'], 'leave_types_org_dept_code_unique');

            // Now we can drop the old more restrictive unique constraint
            $table->dropUnique(['organization_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropUnique('leave_types_org_dept_code_unique');
            $table->unique(['organization_id', 'code'], 'leave_types_organization_id_code_unique');
        });
    }
};
