<?php

namespace App\Notifications;

use App\Models\EmployeLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $statusLabels = [
        0 => 'Pending',
        1 => 'Recommended',
        2 => 'Not Recommended',
        3 => 'Approved',
        4 => 'Rejected',
        5 => 'Cancelled',
    ];

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
        $statusLabel = $this->statusLabels[$this->leaveRequest->status] ?? 'Updated';
        
        return (new MailMessage)
            ->subject('Leave Request Status Update: ' . $statusLabel)
            ->view('admin.emails.leave_status_update', [
                'recipientName' => $notifiable->name,
                'actorName' => $this->actorName,
                'statusLabel' => $statusLabel,
                'leaveType' => optional($this->leaveRequest->leaveType)->name ?? '-',
                'startDate' => $this->leaveRequest->start_date,
                'endDate' => $this->leaveRequest->end_date,
                'duration' => $this->leaveRequest->duration,
                'actionUrl' => url('/admin/my-leaves'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->statusLabels[$this->leaveRequest->status] ?? 'Updated';

        return [
            'title' => 'Leave Status Updated',
            'message' => "Your leave request has been {$statusLabel} by {$this->actorName}.",
            'leave_request_id' => $this->leaveRequest->id,
            'status' => $this->leaveRequest->status,
            'actor_name' => $this->actorName,
        ];
    }
}
