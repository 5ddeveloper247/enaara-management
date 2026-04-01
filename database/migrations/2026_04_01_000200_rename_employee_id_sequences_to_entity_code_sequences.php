<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_id_sequences') && !Schema::hasTable('entity_code_sequences')) {
            Schema::rename('employee_id_sequences', 'entity_code_sequences');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('entity_code_sequences') && !Schema::hasTable('employee_id_sequences')) {
            Schema::rename('entity_code_sequences', 'employee_id_sequences');
        }
    }
};
