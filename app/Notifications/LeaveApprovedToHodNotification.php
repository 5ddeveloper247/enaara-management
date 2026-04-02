<?php

namespace App\Notifications;

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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = optional($this->leaveRequest->fromEmployee)->full_name ?? 'Employee';
        $leaveType = optional($this->leaveRequest->leaveType)->name ?? '-';

        return (new MailMessage)
            ->subject('Employee Leave Approved Notification')
            ->greeting('Hello ' . ($notifiable->name ?? 'Sir/Madam') . ',')
            ->line("{$employeeName}'s leave request has been approved.")
            ->line("Leave Type: {$leaveType}")
            ->line("Start Date: {$this->leaveRequest->start_date}")
            ->line("End Date: {$this->leaveRequest->end_date}")
            ->line("Duration: {$this->leaveRequest->duration} day(s)")
            ->line("The employee will be absent during this period.")
            ->line("Approved by: {$this->actorName}");
            // ->action('View Leave Requests', url('/admin/leave-requests'));
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