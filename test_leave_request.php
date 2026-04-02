<?php

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$tl = Employee::where('name', 'Test Team Lead')->first();
$leaveType = LeaveType::first();

if (!$tl || !$leaveType) {
    echo "Required data (TL or LeaveType) missing.\n";
    exit;
}

echo "Submitting leave request for Employee ID: " . $tl->id . " (Test Team Lead)\n";

$service = app(LeaveRequestService::class);

$validated = [
    'employee_id' => $tl->id,
    'leave_type_id' => $leaveType->id,
    'start_date' => now()->addDays(5)->toDateString(),
    'end_date' => now()->addDays(7)->toDateString(),
    'reason' => 'Testing multiple entries',
];

// Count before
$countBefore = EmployeLeaveRequest::count();

DB::beginTransaction();
try {
    $service->store($validated);
    DB::commit();
    echo "Store call successful.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}

// Count after
$countAfter = EmployeLeaveRequest::count();
$createdCount = $countAfter - $countBefore;

echo "Total records created: $createdCount\n";

$newRecords = EmployeLeaveRequest::latest('id')->limit($createdCount)->get();
foreach ($newRecords as $record) {
    $toName = optional($record->toEmployee)->name ?? 'None';
    echo "ID: {$record->id}, To: {$toName}, Status: {$record->status}\n";
}
