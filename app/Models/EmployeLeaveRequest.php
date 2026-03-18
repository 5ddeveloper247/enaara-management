<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class EmployeLeaveRequest extends Model
{
    protected $table = 'employe_leave_requests';
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
            // When request becomes approved (status=3), create day-wise leave entities.
            if (!$leaveRequest->wasChanged('status') || (int) $leaveRequest->status !== 3) {
                return;
            }

            if (!$leaveRequest->start_date || !$leaveRequest->end_date || !$leaveRequest->from_employee_id) {
                return;
            }

            $start = Carbon::parse($leaveRequest->start_date)->startOfDay();
            $end = Carbon::parse($leaveRequest->end_date)->startOfDay();
            if ($end->lt($start)) {
                return;
            }

            DB::transaction(function () use ($leaveRequest, $start, $end) {
                $rows = [];
                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $rows[] = [
                        'leave_request_id' => $leaveRequest->id,
                        'employee_id' => $leaveRequest->from_employee_id,
                        'leave_type_id' => $leaveRequest->leave_type_id,
                        'leave_date' => $cursor->toDateString(),
                        'start_date' => $cursor->toDateString(),
                        'end_date' => $cursor->toDateString(),
                        'status' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $cursor->addDay();
                }

                // Avoid duplicates if approved twice.
                EmployeLeaveEntity::query()->upsert(
                    $rows,
                    ['leave_request_id', 'leave_date'],
                    ['employee_id', 'leave_type_id', 'status', 'updated_at']
                );
            });
        });
    }
}
