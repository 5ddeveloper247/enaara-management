<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveBalanceAdjustment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BalanceTrackerService
{
    public function getBalances($organization = null, $department = null)
    {
        try {
            $currentYear = Carbon::now()->year;
            // Load active employees with their current-year leave quotas
            $employees = Employee::with(['organization', 'department'])
                ->where('is_active', 1)
                ->when($organization, function ($query) use ($organization) {
                    $query->whereHas('organization', function ($q) use ($organization) {
                        $q->where('name', $organization);
                    });
                })
                ->when($department, function ($query) use ($department) {
                    $query->whereHas('department', function ($q) use ($department) {
                        $q->where('name', $department);
                    });
                })
                ->get();
            // dd($employees);
            return $employees->map(function ($employee) use ($currentYear) {
                $annual = ['earned' => 0, 'used' => 0, 'remaining' => 0];
                $sick = ['earned' => 0, 'used' => 0, 'remaining' => 0];
                $casual = ['earned' => 0, 'used' => 0, 'remaining' => 0];
                // dd($employee);
                $quotas = EmployeeLeaveQuota::with('leaveType')
                    ->where('employee_id', $employee->id)
                    ->where('year', $currentYear)
                    ->get();
                // dd($quotas);
                foreach ($quotas as $quota) {
                    $type = strtolower($quota->leaveType->name ?? '');

                    // Fetch adjustments for this employee and leave type
                    $adjustments = LeaveBalanceAdjustment::where('employee_id', $employee->id)
                        ->where('leave_type_id', $quota->leave_type_id)
                        ->whereYear('created_at', $currentYear)
                        ->selectRaw("
                        SUM(CASE 
                            WHEN adjustment_type = 'add' THEN days 
                            ELSE -days 
                        END) as total_adjustment
                    ")
                        ->value('total_adjustment') ?? 0;

                    $totalEarned = (float) $quota->quota + (float) $adjustments;
                    $totalUsed = (float) $quota->used;

                    $quotaData = [
                        'earned' => $totalEarned,
                        'used' => $totalUsed,
                        'remaining' => (float) ($totalEarned - $totalUsed)
                    ];

                    if (str_contains($type, 'annual')) {
                        $annual = $quotaData;
                    } elseif (str_contains($type, 'sick')) {
                        $sick = $quotaData;
                    } elseif (str_contains($type, 'casual') || str_contains($type, 'causal')) {
                        $casual = $quotaData;
                    }
                }

                return [
                    'id' => $employee->id,
                    'employeeName' => $employee->name,
                    'employeeId' => $employee->employee_code ?? ('EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)),
                    'joinDate' => $employee->join_date ?? $employee->created_at,
                    'organization' => $employee->organization->name ?? 'N/A',
                    'department' => $employee->department->name ?? 'N/A',
                    'annual' => $annual,
                    'sick' => $sick,
                    'casual' => $casual,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching balance tracker data: ' . $e->getMessage(), [
                'organization' => $organization,
                'department' => $department,
                'trace' => $e->getTraceAsString()
            ]);
            return collect([]); // Return empty collection on failure
        }
    }

    public function adjustBalance(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                $employeeId = $data['employeeId'];
                $adjustmentType = $data['adjustmentType'];
                $days = (float) $data['days'];
                $reason = $data['reason'];
                $leaveTypeString = strtolower($data['leaveType']);
                $currentYear = Carbon::now()->year;
                $employee = Employee::with(['organization', 'department'])->findOrFail($employeeId);
                $leaveQuotas = EmployeeLeaveQuota::with('leaveType')
                    ->where('employee_id', $employeeId)
                    ->where('year', $currentYear)
                    ->get();
                // dd($leaveQuotas);
                // Find correct leave type quota
                $targetQuota = null;

                foreach ($leaveQuotas as $quota) {
                    $type = strtolower($quota->leaveType->name ?? '');
                    if (
                        str_contains($type, $leaveTypeString) ||
                        ($leaveTypeString === 'casual' && str_contains($type, 'causal'))
                    ) {
                        $targetQuota = $quota;
                        break;
                    }
                }

                if (!$targetQuota) {
                    throw new \Exception("Could not locate active '{$leaveTypeString}' leave quota for this employee.");
                }

                // Calculate current adjusted earned/remaining
                $adjustments = LeaveBalanceAdjustment::where('employee_id', $employeeId)
                    ->where('leave_type_id', $targetQuota->leave_type_id)
                    ->whereYear('created_at', $currentYear)
                    ->selectRaw("
                SUM(CASE 
                    WHEN adjustment_type = 'add' THEN days 
                    ELSE -days 
                END) as total_adjustment
            ")
                    ->value('total_adjustment') ?? 0;

                $currentEarned = (float) $targetQuota->quota + (float) $adjustments;
                $currentUsed = (float) $targetQuota->used;
                $currentRemaining = $currentEarned - $currentUsed;

                // Validation based on adjustment type
                if ($adjustmentType === 'add') {
                    // Adding to Quota (Always allowed)
                } elseif ($adjustmentType === 'subtract') {
                    // Subtracting from Quota - Ensure remaining doesn't go below zero
                    if (($currentRemaining - $days) < 0) {
                        throw new \Exception("Cannot subtract more from quota. Remaining balance cannot be less than zero. (Current remaining: {$currentRemaining} days).");
                    }
                }

                LeaveBalanceAdjustment::create([
                    'employee_id' => $employeeId,
                    'organization_id' => $employee->organization_id,
                    'department_id' => $employee->department_id,
                    'leave_type_id' => $targetQuota->leave_type_id,
                    'adjustment_type' => $adjustmentType,
                    'days' => $days,
                    'reason' => $reason,
                    'adjusted_by' => auth()->id(),
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Error adjusting leave balance: ' . $e->getMessage(), [
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw to be caught by Controller
            }
        });
    }
}
