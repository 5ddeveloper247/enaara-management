<?php

namespace App\Services\leaverequestPrivatefunctions;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Notifications\Notification;

class LeaveRequestNotifier
{
    public function notifyApprover(Employee $approverEmployee, Notification $notification, bool $requireActiveUser = false): void
    {
        $userQuery = User::query()->where('employee_id', $approverEmployee->id);

        if ($requireActiveUser) {
            $userQuery->where('is_active', true);
        }

        $user = $userQuery->first();

        if ($user) {
            $user->notify($notification);

            return;
        }

        $email = trim((string) ($approverEmployee->email ?? ''));

        if ($email === '') {
            return;
        }

        $approverEmployee->notify($notification);
    }

    public function notifyEmployeeById(?int $employeeId, Notification $notification, bool $requireActiveUser = false): void
    {
        if ($employeeId === null || $employeeId <= 0) {
            return;
        }

        $employee = Employee::query()
            ->where('id', $employeeId)
            ->where('is_active', true)
            ->first();

        if ($employee === null) {
            return;
        }

        $this->notifyApprover($employee, $notification, $requireActiveUser);
    }
}
