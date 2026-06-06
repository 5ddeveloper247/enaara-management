<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('shift_roster_approval_segment_id')->nullable()->after('shift_roster_approval_request_id');
            $table->foreign('shift_roster_approval_segment_id', 'sr_entries_approval_segment_fk')
                ->references('id')
                ->on('shift_roster_approval_segments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shift_roster_entries', function (Blueprint $table) {
            $table->dropForeign(['shift_roster_approval_segment_id']);
            $table->dropColumn('shift_roster_approval_segment_id');
        });
    }
};
