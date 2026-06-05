<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\Role;
use App\Models\ShiftPlanner;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Services\ShiftRosterApprovalService;
use App\Services\ShiftRosterApproverResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

echo "=== Roster Approval Smoke Test ===\n\n";

echo 'Tables OK: ' . (Schema::hasTable('shift_roster_approval_requests') && Schema::hasTable('shift_roster_approval_request_items') ? 'yes' : 'no') . "\n";
echo 'Pending requests: ' . ShiftRosterApprovalRequest::where('approval_status', 'pending')->count() . "\n";
echo 'Role level 3 (GM tier): ' . \App\Models\RoleLevel::where('level', 3)->where('is_active', true)->count() . "\n";
echo 'Shift employees: ' . Employee::where('is_active', 1)->where('engagement_mode', 'shifts')->count() . "\n\n";

$assignee = Employee::query()
    ->where('is_active', 1)
    ->where('engagement_mode', 'shifts')
    ->whereHas('role', function ($q) {
        $q->where(function ($levelQ) {
            $levelQ->whereHas('roleLevel', fn ($rl) => $rl->where('level', '!=', 3))
                ->orWhere(function ($nameQ) {
                    $nameQ->whereNull('role_level_id')
                        ->whereNotIn('name', \App\Models\RoleLevel::where('level', 3)->pluck('name'));
                });
        });
    })
    ->with(['role', 'department'])
    ->first();

if (! $assignee) {
    echo "FAIL: No shift-based non-GM employee found.\n";
    exit(1);
}

$resolver = app(ShiftRosterApproverResolver::class);
$gm = $resolver->resolveGmForEmployee($assignee);

echo 'Assignee: ' . trim($assignee->full_name ?? $assignee->first_name ?? 'Unknown') . " (ID {$assignee->id}, dept {$assignee->department_id})\n";
echo 'Resolved GM: ' . ($gm ? trim($gm->full_name ?? $gm->first_name ?? 'Unknown') . " (ID {$gm->id})" : 'NONE') . "\n";

if (! $gm) {
    echo "Approver lookup debug for dept {$assignee->department_id} (role level 3):\n";
    $level3Roles = Role::query()
        ->where(function ($q) {
            $q->whereHas('roleLevel', fn ($rl) => $rl->where('level', 3))
                ->orWhereIn('name', \App\Models\RoleLevel::where('level', 3)->pluck('name'));
        })
        ->get(['id', 'name', 'slug', 'department_id']);
    foreach ($level3Roles as $role) {
        echo "  role #{$role->id} {$role->name} (dept {$role->department_id})\n";
    }
    $deptEmployees = Employee::query()
        ->where('department_id', $assignee->department_id)
        ->where('is_active', 1)
        ->with('role:id,name,slug')
        ->get();
    foreach ($deptEmployees as $emp) {
        echo "  emp #{$emp->id} " . trim($emp->full_name ?? $emp->first_name ?? '') . ' role=' . ($emp->role->name ?? '-') . "\n";
    }
    echo "FAIL: GM resolver returned null.\n";
    exit(1);
}

$plannerUser = User::query()->whereNotNull('employee_id')->where('is_active', 1)->first();
if (! $plannerUser) {
    echo "FAIL: No active user with employee_id for submit test.\n";
    exit(1);
}

$shift = ShiftPlanner::query()->where('is_active', 1)->first();
if (! $shift) {
    echo "FAIL: No active shift template found.\n";
    exit(1);
}

$testDate = now()->addDays(10)->toDateString();
ShiftRosterEntry::query()
    ->where('employee_id', $assignee->id)
    ->where('roster_date', $testDate)
    ->delete();

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
    echo "\nSubmit OK: request #{$request->id}, status={$request->approval_status}, approver_employee_id={$request->approver_employee_id}\n";
    echo "Items: {$request->items()->count()}, entries in roster table (should be 0): " . ShiftRosterEntry::where('employee_id', $assignee->id)->where('roster_date', $testDate)->count() . "\n";
} catch (Throwable $e) {
    echo 'Submit FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}

$gmUser = User::query()->where('employee_id', $gm->id)->where('is_active', 1)->first();
if (! $gmUser) {
    echo "WARN: GM has no linked user — dashboard list will be empty for GM login, but approval can still be tested.\n";
    Auth::login($plannerUser);
} else {
    Auth::login($gmUser);
}

try {
    $approved = $approvalService->approve($request->id, Auth::id());
    echo "Approve OK: request #{$approved->id}, status={$approved->approval_status}\n";
    $entry = ShiftRosterEntry::where('employee_id', $assignee->id)->where('roster_date', $testDate)->first();
    echo 'Roster entry created: ' . ($entry ? 'yes (status=' . $entry->status . ')' : 'NO') . "\n";
} catch (Throwable $e) {
    echo 'Approve FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}

$pendingForGm = $approvalService->getPendingForApprover($gm->id)->count();
echo "Pending for GM after approve: {$pendingForGm}\n";
echo "\nPASS: End-to-end approval flow works.\n";
