<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApprovedToHodNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EmployeLeaveRequest $leaveRequest,
        public string $actorName
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
        $employee     = $this->leaveRequest->fromEmployee;
        $employeeId   = $employee?->employee_code ?? '-';
        $employeeName = $employee?->full_name ?? $employee?->name ?? 'An employee';
        $deptName     = optional($employee?->department)->name ?? '-';

        return (new MailMessage)
            ->subject('Employee Leave Approved Notification')
            ->view('admin.emails.leave_approved_hod', [
                'recipientName'  => $notifiable->name,
                'senderName'     => $employeeName,
                'employeeId'     => $employeeId,
                'departmentName' => $deptName,
                'leaveType'      => optional($this->leaveRequest->leaveType)->name ?? '-',
                'startDate'      => $this->leaveRequest->start_date?->format('d M, Y') ?? $this->leaveRequest->start_date,
                'endDate'        => $this->leaveRequest->end_date?->format('d M, Y') ?? $this->leaveRequest->end_date,
                'duration'       => (float) $this->leaveRequest->duration,
                'actorName'      => $this->actorName,
                'actionUrl'      => url('/admin/leave-request'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $employeeName = optional($this->leaveRequest->fromEmployee)->full_name ?? 'Employee';

        return [
            'title' => 'Employee Leave Approved',
            'message' => "{$employeeName}'s leave has been approved. The employee will be absent from {$this->leaveRequest->start_date} to {$this->leaveRequest->end_date}.",
            'leave_request_id' => $this->leaveRequest->id,
            'employee_id' => $this->leaveRequest->from_employee_id,
            'employee_name' => $employeeName,
            'status' => $this->leaveRequest->status,
            'actor_name' => $this->actorName,
            'url' => '/admin/leave-request',
        ];
    }
}
