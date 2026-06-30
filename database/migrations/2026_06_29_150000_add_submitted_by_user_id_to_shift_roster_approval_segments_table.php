<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_roster_approval_segments', function (Blueprint $table) {
            if (! Schema::hasColumn('shift_roster_approval_segments', 'submitted_by_user_id')) {
                $table->foreignId('submitted_by_user_id')
                    ->nullable()
                    ->after('approver_employee_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        $this->backfillSubmittedByFromEntries();
    }

    public function down(): void
    {
        Schema::table('shift_roster_approval_segments', function (Blueprint $table) {
            if (Schema::hasColumn('shift_roster_approval_segments', 'submitted_by_user_id')) {
                $table->dropConstrainedForeignId('submitted_by_user_id');
            }
        });
    }

    private function backfillSubmittedByFromEntries(): void
    {
        if (! Schema::hasColumn('shift_roster_approval_segments', 'submitted_by_user_id')) {
            return;
        }

        $segmentIds = DB::table('shift_roster_approval_segments')
            ->whereNull('submitted_by_user_id')
            ->pluck('id');

        foreach ($segmentIds as $segmentId) {
            $submittedByUserId = DB::table('shift_roster_entries')
                ->select('created_by', DB::raw('COUNT(*) as entry_count'))
                ->where('shift_roster_approval_segment_id', $segmentId)
                ->whereNotNull('created_by')
                ->groupBy('created_by')
                ->orderByDesc('entry_count')
                ->value('created_by');

            if (! $submittedByUserId) {
                $submittedByUserId = DB::table('shift_roster_approval_segments as segments')
                    ->join(
                        'shift_roster_approval_requests as requests',
                        'requests.id',
                        '=',
                        'segments.shift_roster_approval_request_id'
                    )
                    ->where('segments.id', $segmentId)
                    ->value('requests.requested_by');
            }

            if ($submittedByUserId) {
                DB::table('shift_roster_approval_segments')
                    ->where('id', $segmentId)
                    ->update(['submitted_by_user_id' => $submittedByUserId]);
            }
        }
    }
};
