<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class EmployeLeaveRequest extends Model
{
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
            // When request becomes approved (status=3), create day-wise leave entities. Only for Approver rows.
            if ((int) $leaveRequest->status === 3 && in_array((int) $leaveRequest->action_type, [0, 2])) {
                if (!$leaveRequest->start_date || !$leaveRequest->end_date || !$leaveRequest->from_employee_id) {
                    return;
                }

                $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
                $end = Carbon::parse($leaveRequest->end_date)->startOfDay();
                if ($end->lt($start)) {
                    return;
                }

                DB::transaction(function () use ($leaveRequest, $start, $end) {
                    // First clear any existing entities (in case dates changed or we are re-approving)
                    $leaveRequest->leaveEntities()->delete();

                    $rows = [];
                    $cursor = $start->copy();
                    
                    // Fallback to employee's department if not set on the request (e.g. for older records)
                    $deptId = $leaveRequest->department_id;
                    if (!$deptId) {
                        $deptId = Employee::where('id', $leaveRequest->from_employee_id)->value('department_id');
                    }

                    while ($cursor->lte($end)) {
                        $rows[] = [
                            'leave_request_id' => $leaveRequest->id,
                            'employee_id' => $leaveRequest->from_employee_id,
                            'leave_type_id' => $leaveRequest->leave_type_id,
                            'department_id' => $deptId,
                            'leave_date' => $cursor->toDateString(),
                            'start_date' => $cursor->toDateString(),
                            'end_date' => $cursor->toDateString(),
                            'duration' => 1,
                            'status' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $cursor->addDay();
                    }

                    // Insert fresh entities
                    EmployeLeaveEntity::query()->insert($rows);
                });
            } else {
                // If the request is not approved (or no longer approved), delete any entities.
                $leaveRequest->leaveEntities()->delete();
            }
        });
    }
}
