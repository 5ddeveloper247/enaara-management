<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_roster_entries', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('is_compensatory_earned')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('shift_roster_entries', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('shift_roster_entries', 'assigned_by')) {
                $table->foreignId('assigned_by')
                    ->nullable()
                    ->after('updated_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            if (Schema::hasColumn('shift_roster_entries', 'assigned_by')) {
                $table->dropConstrainedForeignId('assigned_by');
            }
            if (Schema::hasColumn('shift_roster_entries', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('shift_roster_entries', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};

