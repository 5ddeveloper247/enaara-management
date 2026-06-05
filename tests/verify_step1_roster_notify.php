<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Services\ShiftRosterApprovalService;
use App\Services\ShiftRosterApproverResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Step 1 Verification: Submit → Dept GM → Notify + Email ===\n\n";

$checks = [];

function check(string $label, bool $pass, string $detail = ''): void
{
    global $checks;
    $checks[] = ['label' => $label, 'pass' => $pass, 'detail' => $detail];
    echo ($pass ? '[PASS]' : '[FAIL]') . " {$label}";
    if ($detail !== '') {
        echo " — {$detail}";
    }
    echo "\n";
}

// --- Setup: find assignee whose dept has a level-3 GM ---
$resolver = app(ShiftRosterApproverResolver::class);

$assignee = Employee::query()
    ->where('is_active', 1)
    ->where('engagement_mode', 'shifts')
    ->whereNotNull('department_id')
    ->get()
    ->first(function (Employee $emp) use ($resolver) {
        $gm = $resolver->resolveGmForEmployee($emp);

        return $gm !== null
            && ! empty($emp->department_id)
            && (int) $gm->department_id === (int) $emp->department_id;
    });

if (! $assignee) {
    echo "WARN: No assignee found with a level-3 GM in the SAME department.\n";
    echo "Testing with SBU/org fallback instead...\n\n";

    $assignee = Employee::query()
        ->where('is_active', 1)
        ->where('engagement_mode', 'shifts')
        ->first();

    if (! $assignee) {
        echo "FAIL: No shift-based employee found.\n";
        exit(1);
    }
}

$expectedGm = $resolver->resolveGmForEmployee($assignee);
$gmUser = $expectedGm ? User::where('employee_id', $expectedGm->id)->first() : null;
$plannerUser = User::query()->where('is_active', 1)->whereNotNull('employee_id')->first();
$shift = ShiftPlanner::query()->where('is_active', 1)->first();

echo "Assignee: #{$assignee->id} " . trim($assignee->full_name ?? '') . " (dept {$assignee->department_id})\n";
echo "Expected GM: " . ($expectedGm ? "#{$expectedGm->id} " . trim($expectedGm->full_name ?? '') . " (dept {$expectedGm->department_id}, role level via resolver)" : 'NONE') . "\n";
echo "GM linked user: " . ($gmUser ? "#{$gmUser->id} {$gmUser->name}" : 'NONE') . "\n";
echo "GM email on employee: " . ($expectedGm?->email ?: 'NONE') . "\n\n";

if (! $expectedGm) {
    check('GM resolver finds approver', false, 'No level-3 employee in scope');
    exit(1);
}

$sameDept = (int) $expectedGm->department_id === (int) $assignee->department_id;
check(
    'GM is from assignee department (when dept GM exists)',
    $sameDept || Employee::query()
        ->select('employees.*')
        ->join('roles as r', 'r.id', '=', 'employees.role_id')
        ->where('employees.is_active', true)
        ->where('employees.department_id', $assignee->department_id)
        ->where(function ($levelQuery) {
            $levelQuery->whereHas('role.roleLevel', fn ($rl) => $rl->where('level', 3))
                ->orWhereExists(function ($sub) {
                    $sub->from('role_levels as rl')
                        ->whereColumn('rl.id', 'r.role_level_id')
                        ->where('rl.level', 3);
                });
        })->doesntExist(),
    $sameDept
        ? "GM dept {$expectedGm->department_id} = assignee dept {$assignee->department_id}"
        : 'No level-3 in assignee dept — resolver correctly fell back to SBU/org'
);

if (! $plannerUser || ! $shift) {
    echo "FAIL: Missing planner user or shift template.\n";
    exit(1);
}

$testDate = now()->addDays(20)->toDateString();
ShiftRosterEntry::query()
    ->where('employee_id', $assignee->id)
    ->where('roster_date', $testDate)
    ->delete();

$notificationsBefore = Schema::hasTable('notifications')
    ? DB::table('notifications')->count()
    : 0;
$jobsBefore = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

Auth::login($plannerUser);

/** @var ShiftRosterApprovalService $approvalService */
$approvalService = app(ShiftRosterApprovalService::class);

try {
    $request = $approvalService->submitSingle([
        'employee_type' => 'employee',
        'employee_id' => $assignee->id,
        'roster_date' => $testDate,
        'shift_planner_id' => $shift->id,
        'status' => 1,
    ]);
} catch (Throwable $e) {
    check('Roster submit creates approval request', false, $e->getMessage());
    exit(1);
}

check(
    'Roster submit creates approval request',
    $request instanceof ShiftRosterApprovalRequest && $request->approval_status === 'pending',
    "request #{$request->id}"
);

check(
    'Request assigned to resolved GM',
    (int) $request->approver_employee_id === (int) $expectedGm->id,
    "approver_employee_id={$request->approver_employee_id}, expected={$expectedGm->id}"
);

check(
    'No direct roster entry before approval',
    ! ShiftRosterEntry::where('employee_id', $assignee->id)->where('roster_date', $testDate)->exists(),
    'shift_roster_entries empty for test date'
);

// Notification check
$notificationsAfter = Schema::hasTable('notifications') ? DB::table('notifications')->count() : 0;
$jobsAfter = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

$notifForGm = 0;
if ($gmUser && Schema::hasTable('notifications')) {
    $notifForGm = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $gmUser->id)
        ->where('type', 'like', '%ShiftRosterApprovalRequired%')
        ->count();
}

$queuedJobs = Schema::hasTable('jobs')
    ? DB::table('jobs')->where('queue', 'notifications')->count()
    : 0;

if ($gmUser) {
    $newNotifs = $notificationsAfter - $notificationsBefore;
    $newJobs = $jobsAfter - $jobsBefore;

    if ($newNotifs > 0 && $notifForGm > 0) {
        check('System notification sent to GM user', true, "GM user #{$gmUser->id} has ShiftRosterApprovalRequired notification");
    } elseif ($newJobs > 0 || $queuedJobs > 0) {
        check(
            'System notification sent to GM user',
            true,
            "Notification queued (jobs +{$newJobs}, notifications queue has {$queuedJobs}) — run queue:work to deliver"
        );
    } else {
        check('System notification sent to GM user', false, 'No new notification or queued job found');
    }
} else {
    check(
        'System notification sent to GM user',
        false,
        'GM has no linked user account — notification cannot reach dashboard'
    );
}

// Email path check (code review + notifiable)
$canEmailViaUser = $gmUser && ! empty($gmUser->email);
$canEmailViaEmployee = ! empty(trim((string) ($expectedGm->email ?? '')));
check(
    'Email delivery path exists for GM',
    $canEmailViaUser || $canEmailViaEmployee,
    $canEmailViaUser
        ? "via user email {$gmUser->email}"
        : ($canEmailViaEmployee ? "via employee email {$expectedGm->email}" : 'no email on user or employee record')
);

$notifInstance = new \App\Notifications\ShiftRosterApprovalRequiredNotification($request);
$viaChannels = $notifInstance->via($gmUser ?? new User());
check(
    'Notification uses database + mail channels',
    in_array('database', $viaChannels, true) && in_array('mail', $viaChannels, true),
    implode(', ', $viaChannels)
);

check(
    'Notification is queued on notifications queue',
    $notifInstance instanceof \Illuminate\Contracts\Queue\ShouldQueue,
    'requires php artisan queue:work for async delivery'
);

echo "\n=== Summary ===\n";
$failed = array_filter($checks, fn ($c) => ! $c['pass']);
if ($failed === []) {
    echo "Step 1 flow is WORKING in code + runtime.\n";
} else {
    echo count($failed) . " check(s) failed:\n";
    foreach ($failed as $f) {
        echo "  - {$f['label']}: {$f['detail']}\n";
    }
}

echo "\nCleanup: deleting test request #{$request->id}\n";
$request->items()->delete();
$request->delete();
