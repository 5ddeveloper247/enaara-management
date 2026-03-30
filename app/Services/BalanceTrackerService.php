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
            // Fetch all quotas for the current year, filtered by Org/Dept if provided
            $quotas = EmployeeLeaveQuota::with(['employee.organization', 'employee.department', 'leaveType'])
                ->where('year', $currentYear)
                ->whereHas('employee', function ($query) use ($organization, $department) {
                    $query->where('is_active', 1);
                    if ($organization) {
                        $query->whereHas('organization', fn($q) => $q->where('name', $organization));
                    }
                    if ($department) {
                        $query->whereHas('department', fn($q) => $q->where('name', $department));
                    }
                })
                ->get();

            // Group quotas by employee to build the expected structure
            $groupedByEmployee = $quotas->groupBy('employee_id');

            return $groupedByEmployee->map(function ($employeeQuotas) {
                $employee = $employeeQuotas->first()->employee;
                
                $annual = ['earned' => 0, 'used' => 0, 'remaining' => 0];
                $sick = ['earned' => 0, 'used' => 0, 'remaining' => 0];
                $casual = ['earned' => 0, 'used' => 0, 'remaining' => 0];

                foreach ($employeeQuotas as $quota) {
                    $type = strtolower($quota->leaveType->name ?? '');

                    // Strict matching: Only show if quota's Org/Dept matches employee's current Org/Dept
                    if ($quota->leaveType->organization_id != $employee->organization_id || 
                        $quota->leaveType->department_id != $employee->department_id) {
                        continue;
                    }

                    $quotaData = [
                        'earned' => (float) $quota->adjusted_quota,
                        'used' => (float) $quota->used,
                        'remaining' => (float) $quota->remaining_balance
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
            })->values(); // Reset keys for JSON response
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
                
                // Find correct leave type quota that matches employee's current Org/Dept
                $targetQuota = EmployeeLeaveQuota::with('leaveType')
                    ->where('employee_id', $employeeId)
                    ->where('year', $currentYear)
                    ->whereHas('leaveType', function($query) use ($employee) {
                        $query->where('organization_id', $employee->organization_id)
                              ->where('department_id', $employee->department_id);
                    })
                    ->get()
                    ->filter(function($quota) use ($leaveTypeString) {
                        $type = strtolower($quota->leaveType->name ?? '');
                        return str_contains($type, $leaveTypeString) || 
                               ($leaveTypeString === 'casual' && str_contains($type, 'causal'));
                    })
                    ->first();

                if (!$targetQuota) {
                    throw new \Exception("Could not locate active '{$leaveTypeString}' leave quota for this employee in their current department.");
                }

                $currentEarned = (float) $targetQuota->adjusted_quota;
                $currentUsed = (float) $targetQuota->used;
                $currentRemaining = (float) $targetQuota->remaining_balance;

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
                    'leave_quota_id' => $targetQuota->id,
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
