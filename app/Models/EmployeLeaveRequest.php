<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeLeaveQuota;

use App\Traits\LogsActivity;

class EmployeLeaveRequest extends Model
{
    use LogsActivity;
    protected $fillable = [
        'from_employee_id',
        'to_employee_id',
        'from_user_id',
        'to_user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'reason',
        'status',
        'duration',
        'department_id',
        'action_type',
        'medical_report',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration' => 'decimal:2',
        'status' => 'integer',
        'action_type' => 'integer',
    ];

    public function fromEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function leaveEntities(): HasMany
    {
        return $this->hasMany(EmployeLeaveEntity::class, 'leave_request_id');
    }

    protected static function booted(): void
    {
        static::updated(function (EmployeLeaveRequest $leaveRequest) {
            // If the request is cancelled or rejected, refund any cron-claimed quota and delete entities
            if (in_array((int) $leaveRequest->status, [4, 5], true)) {
                // Refund quota.used for entities that the daily cron already marked as claimed (status=1)
                $claimedByYear = $leaveRequest->leaveEntities()
                    ->where('status', 1)
                    ->selectRaw('employee_id, leave_type_id, YEAR(leave_date) as leave_year, SUM(duration) as total_duration')
                    ->groupBy('employee_id', 'leave_type_id', DB::raw('YEAR(leave_date)'))
                    ->get();

                foreach ($claimedByYear as $row) {
                    EmployeeLeaveQuota::where('employee_id', $row->employee_id)
                        ->where('leave_type_id', $row->leave_type_id)
                        ->where('year', (int) $row->leave_year)
                        ->where('used', '>', 0)
                        ->decrement('used', (float) $row->total_duration);
                }

                $leaveRequest->leaveEntities()->delete();
            }
            // When request becomes approved (status=3), create day-wise leave entities. Only for Approver rows.
            elseif ((int) $leaveRequest->status === 3 && in_array((int) $leaveRequest->action_type, [0, 2])) {
                if (!$leaveRequest->start_date || !$leaveRequest->end_date || !$leaveRequest->from_employee_id) {
                    return;
                }

                $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
                $end = Carbon::parse($leaveRequest->end_date)->startOfDay();
                if ($end->lt($start)) {
                    return;
                }

                $employee = Employee::find($leaveRequest->from_employee_id);
                if (!$employee) {
                    return;
                }

                $activeDates = app(\App\Services\LeaveRequestService::class)->getActiveLeaveDates($employee, $start, $end);

                DB::transaction(function () use ($leaveRequest, $activeDates) {
                    // First clear any existing entities (in case dates changed or we are re-approving)
                    $leaveRequest->leaveEntities()->delete();

                    $rows = [];
                    
                    // Fallback to employee's department if not set on the request (e.g. for older records)
                    $deptId = $leaveRequest->department_id;
                    if (!$deptId) {
                        $deptId = Employee::where('id', $leaveRequest->from_employee_id)->value('department_id');
                    }

                    foreach ($activeDates as $dateStr) {
                        $rows[] = [
                            'leave_request_id' => $leaveRequest->id,
                            'employee_id' => $leaveRequest->from_employee_id,
                            'leave_type_id' => $leaveRequest->leave_type_id,
                            'department_id' => $deptId,
                            'leave_date' => $dateStr,
                            'start_date' => $dateStr,
                            'end_date' => $dateStr,
                            'duration' => 1,
                            'status' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Insert fresh entities
                    if (!empty($rows)) {
                        EmployeLeaveEntity::query()->insert($rows);
                    }
                });
            } else {
                // If the request is not approved (or no longer approved), delete any entities.
                $leaveRequest->leaveEntities()->delete();
            }
        });
    }
}
