<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveSubmittedToHrNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EmployeLeaveRequest $leaveRequest
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof Employee) {
            return ['mail', 'database'];
        }

        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->leaveRequest->loadMissing([
            'fromEmployee.department:id,name',
            'leaveType:id,name',
        ]);

        $employee = $this->leaveRequest->fromEmployee;
        $employeeId = $employee?->employee_code ?? '-';
        $employeeName = $employee?->full_name ?? $employee?->name ?? 'An employee';
        $deptName = optional($employee?->department)->name ?? '-';

        return (new MailMessage)
            ->subject('Leave Submitted - HR Notification')
            ->view('admin.emails.leave_submitted_hr', [
                'recipientName' => $notifiable->name,
                'senderName' => $employeeName,
                'employeeId' => $employeeId,
                'departmentName' => $deptName,
                'leaveType' => optional($this->leaveRequest->leaveType)->name ?? '-',
                'startDate' => $this->leaveRequest->start_date?->format('d M, Y') ?? $this->leaveRequest->start_date,
                'endDate' => $this->leaveRequest->end_date?->format('d M, Y') ?? $this->leaveRequest->end_date,
                'duration' => (float) $this->leaveRequest->duration,
                'actionUrl' => url('/admin/leave-request'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->leaveRequest->loadMissing([
            'fromEmployee.department:id,name',
            'leaveType:id,name',
        ]);

        $employee = $this->leaveRequest->fromEmployee;
        $employeeName = $employee?->full_name ?? $employee?->name ?? 'An employee';
        $deptName = optional($employee?->department)->name ?? 'a department';

        return [
            'title' => 'Leave Request Submitted',
            'message' => $deptName . ' employee ' . $employeeName . ' has submitted a leave request.',
            'leave_request_id' => $this->leaveRequest->id,
            'from_employee_id' => $this->leaveRequest->from_employee_id,
            'leave_type' => optional($this->leaveRequest->leaveType)->name,
            'start_date' => $this->leaveRequest->start_date,
            'end_date' => $this->leaveRequest->end_date,
            'duration' => $this->leaveRequest->duration,
            'status' => $this->leaveRequest->status,
            'url' => '/admin/leave-request',
        ];
    }
}
