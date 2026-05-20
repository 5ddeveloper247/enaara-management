<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\EmployeLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApprovalNotification extends Notification implements ShouldQueue
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
        $employee     = $this->leaveRequest->fromEmployee;
        $employeeId   = $employee?->employee_code ?? '-';
        $employeeName = $employee?->full_name ?? $employee?->name ?? 'An employee';
        $deptName     = optional($employee?->department)->name ?? '-';

        return (new MailMessage)
            ->subject('Leave Request Approval Required')
            ->view('admin.emails.leave_recommendation', [
                'recipientName'  => $notifiable->name,
                'senderName'     => $employeeName,
                'employeeId'     => $employeeId,
                'departmentName' => $deptName,
                'leaveType'      => optional($this->leaveRequest->leaveType)->name ?? '-',
                'startDate'      => $this->leaveRequest->start_date?->format('d M, Y') ?? $this->leaveRequest->start_date,
                'endDate'        => $this->leaveRequest->end_date?->format('d M, Y') ?? $this->leaveRequest->end_date,
                'duration'       => (float) $this->leaveRequest->duration,
                'reason'         => $this->leaveRequest->reason ?? 'No reason provided.',
                'actionUrl'      => url('/admin/leave-request'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Leave Request Approval Required',
            'message' => (optional($this->leaveRequest->fromEmployee)->name ?? 'An employee') . ' submitted a leave request for approval.',
            'leave_request_id' => $this->leaveRequest->id,
            'from_employee_id' => $this->leaveRequest->from_employee_id,
            'to_employee_id' => $this->leaveRequest->to_employee_id,
            'leave_type' => optional($this->leaveRequest->leaveType)->name,
            'start_date' => $this->leaveRequest->start_date,
            'end_date' => $this->leaveRequest->end_date,
            'duration' => $this->leaveRequest->duration,
            'status' => $this->leaveRequest->status,
            'url' => '/admin/leave-request',
        ];
    }
}
