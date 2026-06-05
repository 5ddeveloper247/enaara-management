<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ShiftRosterEntry;
use App\Models\User;
use App\Services\ShiftRosterApprovalService;
use App\Services\ShiftRosterApproverResolver;
use Illuminate\Support\Facades\Auth;

$resolver = app(ShiftRosterApproverResolver::class);
$approvalService = app(ShiftRosterApprovalService::class);

$entry = ShiftRosterEntry::query()->whereNotNull('employee_id')->latest('id')->first();
if (! $entry) {
    echo "No roster entry to test.\n";
    exit(1);
}

$gm = $resolver->resolveGmForEmployee($entry->employee);
$gmUser = $gm ? User::where('employee_id', $gm->id)->first() : null;
$requester = User::where('is_active', 1)->whereNotNull('employee_id')->first();

echo "Entry #{$entry->id} date {$entry->roster_date} status {$entry->status}\n";

Auth::login($requester);
$updateReq = $approvalService->submitUpdate($entry->id, [
    'employee_type' => 'employee',
    'employee_id' => $entry->employee_id,
    'roster_date' => $entry->roster_date->toDateString(),
    'shift_planner_id' => $entry->shift_planner_id,
    'notes' => 'Updated via approval test',
]);
echo "Update request #{$updateReq->id} type={$updateReq->request_type} status={$updateReq->approval_status}\n";

Auth::login($gmUser);
$approved = $approvalService->approve($updateReq->id, $gmUser->id);
echo "Approved update request status={$approved->approval_status}\n";

$entry->refresh();
echo "Entry notes after approve: {$entry->notes}\n";

Auth::login($requester);
$deleteReq = $approvalService->submitDelete($entry->id);
echo "Delete request #{$deleteReq->id} type={$deleteReq->request_type}\n";

Auth::login($gmUser);
$approvalService->approve($deleteReq->id, $gmUser->id);
$stillExists = ShiftRosterEntry::withTrashed()->find($entry->id);
echo 'Entry soft-deleted: ' . ($stillExists && $stillExists->trashed() ? 'yes' : 'no') . "\n";
echo "PASS\n";
