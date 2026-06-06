<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->foreignId('shift_roster_approval_request_id')
                ->nullable()
                ->after('status')
                ->constrained('shift_roster_approval_requests')
                ->nullOnDelete();

            $table->index(
                ['shift_roster_approval_request_id', 'roster_date'],
                'sr_entries_approval_request_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->dropForeign(['shift_roster_approval_request_id']);
            $table->dropIndex('sr_entries_approval_request_date_idx');
            $table->dropColumn('shift_roster_approval_request_id');
        });
    }
};
