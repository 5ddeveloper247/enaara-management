<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\LeaveBalanceAdjustment;
use App\Notifications\LeaveBalanceAdjustmentNotification;
use App\Services\leaverequestPrivatefunctions\EmployeeLeaveQuotaRecords;
use App\Services\leaverequestPrivatefunctions\LeaveRequestLeaveTypeFilter;
use App\Services\leaverequestPrivatefunctions\LeaveRequestNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BalanceTrackerService
{
    public function __construct(
        private LeaveRequestNotifier $leaveRequestNotifier,
        private LeaveRequestLeaveTypeFilter $leaveRequestLeaveTypeFilter,
        private EmployeeLeaveQuotaRecords $employeeLeaveQuotaRecords,
        private EmployeeViewerScopeService $viewerScope,
    ) {}

    public function getBalances($organization = null, $department = null)
    {
        try {
            $currentYear = Carbon::now()->year;

            $employeesQuery = Employee::with(['organization', 'department'])
                ->where('is_active', 1)
                ->where(function ($query) use ($organization, $department) {
                    if ($organization) {
                        $query->whereHas('organization', fn ($q) => $q->where('name', $organization));
                    }
                    if ($department) {
                        $query->whereHas('department', fn ($q) => $q->where('name', $department));
                    }
                });

            $this->viewerScope->applySbuScopeToEmployeeQuery($employeesQuery);

            $employees = $employeesQuery->get();

            $employeeIds = $employees->pluck('id');

            // Fetch all quotas for the current year for these employees
            $quotas = EmployeeLeaveQuota::with(['leaveType'])
                ->where('year', $currentYear)
                ->whereIn('employee_id', $employeeIds)
                ->get()
                ->groupBy('employee_id');

            $leaveTypes = LeaveType::query()
                ->with('setting')
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
                    ->with('setting')
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            }

            $balances = $employees->map(function ($employee) use ($quotas, $leaveTypes, $currentYear) {
                $employeeQuotas = $quotas->get($employee->id, collect());
                $eligibleTypes = $leaveTypes->filter(function ($type) use ($employee, $employeeQuotas, $currentYear) {
                    $quota = $employeeQuotas->where('leave_type_id', $type->id)->first();

                    return $this->leaveRequestLeaveTypeFilter->isEmployeeEligibleForQuotaDisplay(
                        $employee,
                        $type,
                        $currentYear,
                        $quota
                    );
                });

                $summaryByTypeId = collect(
                    $this->employeeLeaveQuotaRecords->buildSummaryForEmployee(
                        $employee->id,
                        $eligibleTypes,
                        $currentYear
                    )
                )->keyBy('id');

                $quotaData = [];

                foreach ($leaveTypes as $type) {
                    $quota = $employeeQuotas->where('leave_type_id', $type->id)->first();

                    if (! $this->leaveRequestLeaveTypeFilter->isEmployeeEligibleForQuotaDisplay($employee, $type, $currentYear, $quota)) {
                        $quotaData[$type->id] = [
                            'eligible' => false,
                            'eligibilityMessage' => $this->leaveRequestLeaveTypeFilter->quotaDisplayEligibilityMessage(
                                $employee,
                                $type,
                                $currentYear,
                                $quota
                            ) ?? 'Not eligible for this leave type.',
                            'earned' => 0,
                            'used' => 0,
                            'remaining' => 0,
                        ];

                        continue;
                    }

                    if ($this->leaveRequestLeaveTypeFilter->isCompensatoryLeaveTypeId($type->id)) {
                        $cplSnapshot = $this->leaveRequestLeaveTypeFilter->buildCompensatoryQuotaSnapshot(
                            $employee->id,
                            $currentYear,
                            $quota
                        );

                        $quotaData[$type->id] = [
                            'eligible' => true,
                            'earned' => $cplSnapshot['earned'],
                            'used' => $cplSnapshot['used'],
                            'remaining' => $cplSnapshot['remaining'],
                        ];

                        continue;
                    }

                    $summaryRow = $summaryByTypeId->get($type->id);

                    $quotaData[$type->id] = [
                        'eligible' => true,
                        'earned' => (float) ($summaryRow['total'] ?? $type->annual_quota),
                        'used' => (float) ($summaryRow['used'] ?? 0),
                        'remaining' => (float) ($summaryRow['remaining'] ?? $type->annual_quota),
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
                $employeeId = (int) $data['employee_id'];
                $this->viewerScope->assertEmployeeIdAccessible($employeeId);

                $adjustmentType = $data['increment_type'];
                $days = round((float) $data['days'], 2);
                $reason = $data['reason'];
                $leaveTypeSearch = $data['leave_type'];
                $currentYear = Carbon::now()->year;
                $employee = Employee::with(['organization', 'department'])->findOrFail($employeeId);

                if (! $employee->organization_id) {
                    throw new \Exception('This employee does not have an organization assigned. Please update the employee profile first.');
                }

                if (! $employee->department_id) {
                    throw new \Exception('This employee does not have a department assigned. Please update the employee profile first.');
                }

                $leaveType = LeaveType::query()
                    ->with('setting')
                    ->where('is_active', true)
                    ->where('name', 'like', '%' . $leaveTypeSearch . '%')
                    ->first();

                if (! $leaveType) {
                    throw new \Exception("Could not locate active '{$leaveTypeSearch}' leave type.");
                }

                $existingQuota = EmployeeLeaveQuota::query()
                    ->where('employee_id', $employeeId)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $currentYear)
                    ->first();

                $eligibilityMessage = $this->leaveRequestLeaveTypeFilter->quotaDisplayEligibilityMessage(
                    $employee,
                    $leaveType,
                    $currentYear,
                    $existingQuota
                );

                if ($eligibilityMessage !== null) {
                    throw new \Exception($eligibilityMessage);
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

                $snapshot = $this->resolveQuotaSnapshot(
                    $employeeId,
                    $leaveType,
                    $currentYear,
                    $targetQuota
                );

                $currentRemaining = $snapshot['remaining'];

                if ($adjustmentType === 'subtract' && ($currentRemaining - $days) < 0) {
                    throw new \Exception(
                        'Cannot subtract more from quota. Remaining balance cannot be less than zero.'
                        ." (Current remaining: {$currentRemaining} days)."
                    );
                }

                $previousRemaining = $currentRemaining;
                $newRemaining = $adjustmentType === 'add'
                    ? round($previousRemaining + $days, 2)
                    : round($previousRemaining - $days, 2);

                $adjustment = LeaveBalanceAdjustment::create([
                    'employee_id' => $employeeId,
                    'organization_id' => $employee->organization_id,
                    'leave_quota_id' => $targetQuota->id,
                    'department_id' => $employee->department_id,
                    'leave_type_id' => $targetQuota->leave_type_id,
                    'adjustment_type' => $adjustmentType,
                    'days' => $days,
                    'previous_remaining' => $previousRemaining,
                    'new_remaining' => $newRemaining,
                    'reason' => $reason,
                    'adjusted_by' => auth()->id(),
                ]);

                $actorName = auth()->user()?->name ?? 'Administrator';
                $this->leaveRequestNotifier->notifyEmployeeById(
                    $employeeId,
                    new LeaveBalanceAdjustmentNotification(
                        $adjustment,
                        $targetQuota,
                        $previousRemaining,
                        $actorName
                    ),
                    true
                );

                return true;
            } catch (ValidationException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error('Error adjusting leave balance: ' . $e->getMessage(), [
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw to be caught by Controller
            }
        });
    }

    /**
     * @return array{earned: float, used: float, remaining: float}
     */
    private function resolveQuotaSnapshot(
        int $employeeId,
        LeaveType $leaveType,
        int $year,
        ?EmployeeLeaveQuota $quota = null,
    ): array {
        if ($this->leaveRequestLeaveTypeFilter->isCompensatoryLeaveTypeId($leaveType->id)) {
            return $this->leaveRequestLeaveTypeFilter->buildCompensatoryQuotaSnapshot($employeeId, $year, $quota);
        }

        $summary = $this->employeeLeaveQuotaRecords->buildSummaryForEmployee(
            $employeeId,
            collect([$leaveType]),
            $year
        );

        $row = $summary[0] ?? null;

        return [
            'earned' => (float) ($row['total'] ?? $leaveType->annual_quota),
            'used' => (float) ($row['used'] ?? 0),
            'remaining' => (float) ($row['remaining'] ?? $leaveType->annual_quota),
        ];
    }
}
