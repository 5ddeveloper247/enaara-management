<?php

namespace App\Notifications;

use App\Models\EmployeLeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveHrDelegatedActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private array $statusLabels = [
        0 => 'Pending',
        1 => 'Recommended',
        2 => 'Not Recommended',
        3 => 'Approved',
        4 => 'Rejected',
        5 => 'Cancelled',
    ];

    public function __construct(
        public EmployeLeaveRequest $leaveRequest,
        public string $hrActorName
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->statusLabels[(int) $this->leaveRequest->status] ?? 'Updated';
        $startDate = $this->leaveRequest->start_date instanceof \Illuminate\Support\Carbon
            ? $this->leaveRequest->start_date->format('d M, Y')
            : $this->leaveRequest->start_date;
        $endDate = $this->leaveRequest->end_date instanceof \Illuminate\Support\Carbon
            ? $this->leaveRequest->end_date->format('d M, Y')
            : $this->leaveRequest->end_date;

        return (new MailMessage)
            ->subject('Leave Request Action Taken on Your Behalf')
            ->view('admin.emails.leave_hr_delegated_action', [
                'recipientName' => $notifiable->name,
                'hrActorName' => $this->hrActorName,
                'statusLabel' => $statusLabel,
                'applicantName' => optional($this->leaveRequest->fromEmployee)->full_name ?? 'An employee',
                'leaveType' => optional($this->leaveRequest->leaveType)->name ?? '-',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'duration' => $this->leaveRequest->duration,
                'actionUrl' => url('/admin/leave-request'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->statusLabels[(int) $this->leaveRequest->status] ?? 'Updated';

        return [
            'title' => 'Leave Action Taken on Your Behalf',
            'message' => "{$this->hrActorName} (Human Resource) has {$statusLabel} a leave request on your behalf.",
            'leave_request_id' => $this->leaveRequest->id,
            'status' => $this->leaveRequest->status,
            'hr_actor_name' => $this->hrActorName,
            'url' => '/admin/leave-request',
        ];
    }
}
