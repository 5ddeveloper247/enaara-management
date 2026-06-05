<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Employee;
use App\Models\ShiftPlanner;
use App\Models\User;
use App\Notifications\ShiftRosterRejectedNotification;
use App\Services\ShiftRosterApprovalService;
use App\Services\ShiftRosterApproverResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$resolver = app(ShiftRosterApproverResolver::class);
$approvalService = app(ShiftRosterApprovalService::class);
$assignee = Employee::query()->where('is_active', 1)->where('engagement_mode', 'shifts')->first();
$gm = $assignee ? $resolver->resolveGmForEmployee($assignee) : null;
$gmUser = $gm ? User::where('employee_id', $gm->id)->where('is_active', 1)->first() : null;
$requesterUser = User::query()->where('is_active', 1)->whereNotNull('employee_id')->first();
$shift = ShiftPlanner::query()->where('is_active', 1)->first();

Auth::login($requesterUser);
$request = $approvalService->submitSingle([
    'employee_type' => 'employee',
    'employee_id' => $assignee->id,
    'roster_date' => now()->addDays(27)->toDateString(),
    'shift_planner_id' => $shift->id,
    'status' => 1,
]);

$jobsBefore = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
Auth::login($gmUser);
$rejected = $approvalService->reject($request->id, 'Schedule conflict with policy', $gmUser->id);
$jobsAfter = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;

$notif = new ShiftRosterRejectedNotification($rejected, $gmUser->name);
$via = $notif->via($requesterUser);

echo 'Status: ' . $rejected->approval_status . "\n";
echo 'Channels: ' . implode(', ', $via) . "\n";
echo 'Queued jobs added: ' . ($jobsAfter - $jobsBefore) . "\n";
echo ($jobsAfter > $jobsBefore ? 'PASS' : 'CHECK') . ": Reject notification queued for requester\n";

$request->items()->delete();
$request->delete();
