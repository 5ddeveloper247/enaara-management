<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\ShiftRosterApprovalRequest;
use App\Models\User;
use App\Services\ShiftRosterApprovalService;

echo "=== Roster Dashboard Debug ===\n\n";

$pending = ShiftRosterApprovalRequest::where('approval_status', 'pending')->get();
echo 'Pending requests in DB: ' . $pending->count() . "\n";
foreach ($pending as $r) {
    echo "  #{$r->id} approver_employee_id={$r->approver_employee_id} status={$r->approval_status}\n";
}

echo "\nUsers (Muhammad/Ali):\n";
foreach (User::query()->where('name', 'like', '%Muhammad%')->orWhere('name', 'like', '%Ali%')->get() as $u) {
    echo "  user #{$u->id} name={$u->name} employee_id=" . ($u->employee_id ?? 'NULL') . " active={$u->is_active}\n";
}

echo "\nEmployee #44:\n";
$emp44 = Employee::with('role.roleLevel')->find(44);
if ($emp44) {
    echo '  name=' . trim($emp44->full_name ?? $emp44->first_name ?? '') . "\n";
    echo '  role=' . ($emp44->role?->name ?? '-') . ' level=' . ($emp44->role?->resolvedNumericLevel() ?? 'null') . "\n";
}
$user44 = User::where('employee_id', 44)->first();
echo '  linked user: ' . ($user44 ? "#{$user44->id} {$user44->name}" : 'NONE') . "\n";

/** @var ShiftRosterApprovalService $svc */
$svc = app(ShiftRosterApprovalService::class);

foreach ([44, 1, null] as $empId) {
    $count = $svc->getPendingForApprover($empId)->count();
    echo "\ngetPendingForApprover({$empId}): {$count} requests\n";
}

// Department scope check
echo "\nEmployee scope:\n";
foreach ([1, 34, 38, 44] as $id) {
    $e = Employee::find($id);
    if (! $e) {
        continue;
    }
    echo "  emp {$id}: " . trim($e->full_name ?? '') . " dept={$e->department_id} sbu={$e->sbu_id}\n";
}

$resolver = app(\App\Services\ShiftRosterApproverResolver::class);
$assignee = Employee::find(1);
$gm = $resolver->resolveGmForEmployee($assignee);
echo "\nResolver GM for assignee #1: " . ($gm ? "#{$gm->id} {$gm->full_name}" : 'NONE') . "\n";

// Level 3 employees in same dept as assignee 1
$dept = $assignee?->department_id;
echo "\nRole level 3 employees in dept {$dept}:\n";
$level3 = Employee::query()
    ->select('employees.*')
    ->join('roles as r', 'r.id', '=', 'employees.role_id')
    ->where('employees.is_active', true)
    ->where('employees.department_id', $dept)
    ->where(function ($levelQuery) {
        $levelQuery->where(function ($fkQuery) {
            $fkQuery->whereNotNull('r.role_level_id')
                ->whereExists(function ($sub) {
                    $sub->selectRaw('1')->from('role_levels as rl')
                        ->whereColumn('rl.id', 'r.role_level_id')
                        ->where('rl.level', 3);
                });
        });
    })
    ->get();
foreach ($level3 as $e) {
    $u = User::where('employee_id', $e->id)->first();
    echo "  #{$e->id} {$e->full_name} user=" . ($u ? "#{$u->id} {$u->name}" : 'NONE') . "\n";
}

$ali = User::query()->where('name', 'like', '%Muhammad Ali%')->first()
    ?? User::query()->where('name', 'like', '%Ali%')->first();
if ($ali) {
    echo "\nSimulating login as: #{$ali->id} {$ali->name} employee_id=" . ($ali->employee_id ?? 'NULL') . "\n";
    $forAli = $svc->getPendingForApprover($ali->employee_id ? (int) $ali->employee_id : null);
    echo "Pending for this user: {$forAli->count()}\n";
    if ($ali->employee_id && (int) $ali->employee_id !== 44) {
        echo "ISSUE: logged-in user employee_id ({$ali->employee_id}) != approver_employee_id (44)\n";
    }
    if (! $ali->employee_id) {
        echo "ISSUE: user has no employee_id linked — dashboard returns empty list\n";
    }
}
