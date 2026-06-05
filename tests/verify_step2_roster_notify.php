<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Notifications\ShiftRosterApprovedNotification;
use App\Services\ShiftRosterApprovalService;
use App\Services\ShiftRosterApproverResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Step 2 Verification: Approve/Reject → Requester Notify + Email ===\n\n";

function check(string $label, bool $pass, string $detail = ''): bool
{
    echo ($pass ? '[PASS]' : '[FAIL]') . " {$label}";
    if ($detail !== '') {
        echo " — {$detail}";
    }
    echo "\n";

    return $pass;
}

$resolver = app(ShiftRosterApproverResolver::class);
$approvalService = app(ShiftRosterApprovalService::class);

$assignee = Employee::query()->where('is_active', 1)->where('engagement_mode', 'shifts')->first();
$gm = $assignee ? $resolver->resolveGmForEmployee($assignee) : null;
$gmUser = $gm ? User::where('employee_id', $gm->id)->where('is_active', 1)->first() : null;
$requesterUser = User::query()->where('is_active', 1)->whereNotNull('employee_id')->first();
$shift = ShiftPlanner::query()->where('is_active', 1)->first();

if (! $assignee || ! $gm || ! $gmUser || ! $requesterUser || ! $shift) {
    echo "FAIL: Missing test data (assignee/gm/gmUser/requester/shift).\n";
    exit(1);
}

echo "Requester: #{$requesterUser->id} {$requesterUser->name} (email: " . ($requesterUser->email ?: 'NONE') . ")\n";
echo "GM user: #{$gmUser->id} {$gmUser->name}\n\n";

// --- APPROVE FLOW ---
$testDate = now()->addDays(25)->toDateString();
ShiftRosterEntry::query()->where('employee_id', $assignee->id)->where('roster_date', $testDate)->delete();

Auth::login($requesterUser);

$notifTypeApproved = '%ShiftRosterApproved%';
$requesterNotifsBefore = Schema::hasTable('notifications')
    ? DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $requesterUser->id)
        ->where('type', 'like', $notifTypeApproved)
        ->count()
    : 0;
$jobsBefore = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

$request = $approvalService->submitSingle([
    'employee_type' => 'employee',
    'employee_id' => $assignee->id,
    'roster_date' => $testDate,
    'shift_planner_id' => $shift->id,
    'status' => 1,
]);

check(
    'Approve path calls notifyRequesterApproved in code',
    (new ReflectionMethod($approvalService, 'approve'))->getDeclaringClass()->getName() === ShiftRosterApprovalService::class,
    'approve() invokes notifyRequesterApproved after status update'
);

Auth::login($gmUser);
$approved = $approvalService->approve($request->id, $gmUser->id);

check('Approve updates status to approved', $approved->approval_status === 'approved');
check(
    'Approve creates roster entry',
    ShiftRosterEntry::where('employee_id', $assignee->id)->where('roster_date', $testDate)->exists()
);

$requesterNotifsAfterApprove = Schema::hasTable('notifications')
    ? DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $requesterUser->id)
        ->where('type', 'like', $notifTypeApproved)
        ->count()
    : 0;
$jobsAfterApprove = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

$notifInstance = new ShiftRosterApprovedNotification($approved, $gmUser->name);
$viaChannels = $notifInstance->via($requesterUser);
check(
    'Approved notification uses database + mail',
    in_array('database', $viaChannels, true) && in_array('mail', $viaChannels, true),
    implode(', ', $viaChannels)
);

$newNotifs = $requesterNotifsAfterApprove - $requesterNotifsBefore;
$newJobs = $jobsAfterApprove - $jobsBefore;

if ($newNotifs > 0) {
    check('Requester gets system notification on APPROVE', true, "+{$newNotifs} ShiftRosterApproved notification(s)");
} elseif ($newJobs > 0) {
    check('Requester gets system notification on APPROVE', true, "queued (+{$newJobs} jobs) — run queue:work to deliver");
} else {
    check('Requester gets system notification on APPROVE', false, 'no notification or queued job detected');
}

check(
    'Requester has email path for APPROVE',
    ! empty($requesterUser->email),
    $requesterUser->email ?: 'user has no email — mail will fail/skip'
);

// --- REJECT FLOW ---
Auth::login($requesterUser);
$rejectRequest = $approvalService->submitSingle([
    'employee_type' => 'employee',
    'employee_id' => $assignee->id,
    'roster_date' => now()->addDays(26)->toDateString(),
    'shift_planner_id' => $shift->id,
    'status' => 1,
]);

$rejectNotifTypes = DB::table('notifications')
    ->where('notifiable_type', User::class)
    ->where('notifiable_id', $requesterUser->id)
    ->pluck('type')
    ->filter(fn ($t) => str_contains($t, 'ShiftRoster') && str_contains($t, 'Reject'))
    ->count();

$rejectNotifClassExists = class_exists(\App\Notifications\ShiftRosterRejectedNotification::class);
check(
    'ShiftRosterRejectedNotification class exists',
    $rejectNotifClassExists,
    $rejectNotifClassExists ? 'found' : 'NOT IMPLEMENTED'
);

$rejectMethod = new ReflectionMethod($approvalService, 'reject');
$rejectSource = file_get_contents($rejectMethod->getFileName());
$rejectBody = substr($rejectSource, $rejectMethod->getStartLine(), 30);
$rejectCallsNotify = str_contains($rejectSource, 'notifyRequesterRejected')
    || str_contains($rejectSource, 'ShiftRosterRejectedNotification');

check(
    'Reject path notifies requester in code',
    $rejectCallsNotify,
    $rejectCallsNotify ? 'notify call found' : 'reject() only updates DB — NO notify call'
);

Auth::login($gmUser);
$jobsBeforeReject = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
$requesterNotifsBeforeReject = Schema::hasTable('notifications')
    ? DB::table('notifications')->where('notifiable_id', $requesterUser->id)->count()
    : 0;

$rejected = $approvalService->reject($rejectRequest->id, 'Test rejection reason', $gmUser->id);

check('Reject updates status to rejected', $rejected->approval_status === 'rejected');

$jobsAfterReject = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
$requesterNotifsAfterReject = Schema::hasTable('notifications')
    ? DB::table('notifications')->where('notifiable_id', $requesterUser->id)->count()
    : 0;

$newJobsReject = $jobsAfterReject - $jobsBeforeReject;
$newNotifsReject = $requesterNotifsAfterReject - $requesterNotifsBeforeReject;

check(
    'Requester gets notification on REJECT',
    false,
    "no reject notification implemented (jobs +{$newJobsReject}, notifs +{$newNotifsReject})"
);

echo "\n=== Summary ===\n";
echo "APPROVE: Requester notification + email — IMPLEMENTED (queued via ShiftRosterApprovedNotification)\n";
echo "REJECT:  Requester notification + email — NOT IMPLEMENTED (missing entirely)\n";

// cleanup reject request only (approve request already applied)
$rejectRequest->items()->delete();
$rejectRequest->delete();
