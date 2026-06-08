<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
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

            // Fetch all employees based on filter
            $employees = Employee::with(['organization', 'department'])
                ->where('is_active', 1)
                ->where(function ($query) use ($organization, $department) {
                    if ($organization) {
                        $query->whereHas('organization', fn($q) => $q->where('name', $organization));
                    }
                    if ($department) {
                        $query->whereHas('department', fn($q) => $q->where('name', $department));
                    }
                })
                ->get();

            $employeeIds = $employees->pluck('id');

            // Fetch all quotas for the current year for these employees
            $quotas = EmployeeLeaveQuota::with(['leaveType'])
                ->where('year', $currentYear)
                ->whereIn('employee_id', $employeeIds)
                ->get()
                ->groupBy('employee_id');

            $leaveTypes = LeaveType::query()
                ->where('is_active', 1)
                ->when($organization, function ($query) use ($organization) {
                    $query->whereHas('organization', fn ($q) => $q->where('name', $organization));
                })
                ->when($department, function ($query) use ($department) {
                    $query->where(function ($q) use ($department) {
                        $q->whereHas('departments', fn ($dq) => $dq->where('name', $department))
                            ->orWhereDoesntHave('departments');
                    });
                })
                ->orderBy('name')
                ->get();

            if ($leaveTypes->isEmpty()) {
                $leaveTypes = LeaveType::query()
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            }

            $balances = $employees->map(function ($employee) use ($quotas, $leaveTypes) {
                $employeeQuotas = $quotas->get($employee->id, collect());

                $quotaData = [];

                foreach ($leaveTypes as $type) {
                    $quota = $employeeQuotas->where('leave_type_id', $type->id)->first();
                    $defaultEarned = (float) $type->annual_quota;

                    $quotaData[$type->id] = [
                        'earned' => $quota ? (float) $quota->adjusted_quota : $defaultEarned,
                        'used' => $quota ? (float) $quota->used : 0,
                        'remaining' => $quota ? (float) $quota->remaining_balance : $defaultEarned,
                    ];
                }

                return [
                    'id' => $employee->id,
                    'employeeName' => $employee->full_name,
                    'employeeId' => $employee->employee_code ?? ('EMP-' . str_pad($employee->id, 3, '0', STR_PAD_LEFT)),
                    'joinDate' => $employee->join_date ?? $employee->created_at,
                    'organization' => $employee->organization->name ?? 'N/A',
                    'department' => $employee->department->name ?? 'N/A',
                    'quotas' => $quotaData,
                ];
            })->values();

            return [
                'balances' => $balances,
                'leaveTypes' => $leaveTypes
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching balance tracker data: ' . $e->getMessage());
            return [
                'balances' => collect(),
                'leaveTypes' => collect()
            ];
        }
    }

    private function getQuotaData($employeeQuotas, $type)
    {
        $quota = $employeeQuotas->filter(function($q) use ($type) {
            $name = strtolower($q->leaveType->name ?? '');
            return str_contains($name, $type) || ($type === 'casual' && str_contains($name, 'causal'));
        })->first();

        return [
            'earned' => $quota ? (float) $quota->adjusted_quota : 0,
            'used' => $quota ? (float) $quota->used : 0,
            'remaining' => $quota ? (float) $quota->remaining_balance : 0
        ];
    }

    public function adjustBalance(array $data)
    {
        return DB::transaction(function () use ($data) {
            try {
                $employeeId = $data['employee_id'];
                $adjustmentType = $data['increment_type'];
                $days = (float) $data['days'];
                $reason = $data['reason'];
                $leaveTypeSearch = $data['leave_type'];
                $currentYear = Carbon::now()->year;
                $employee = Employee::with(['organization', 'department'])->findOrFail($employeeId);

                $leaveType = LeaveType::query()
                    ->where('is_active', true)
                    ->where('name', 'like', '%' . $leaveTypeSearch . '%')
                    ->first();

                if (! $leaveType) {
                    throw new \Exception("Could not locate active '{$leaveTypeSearch}' leave type.");
                }

                $targetQuota = EmployeeLeaveQuota::firstOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                    ],
                    [
                        'department_id' => $employee->department_id,
                        'quota' => $leaveType->annual_quota,
                        'used' => 0,
                    ]
                );

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
