<?php

namespace App\Notifications;

use App\Models\ShiftRosterApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftRosterRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShiftRosterApprovalRequest $approvalRequest,
        public string $approverName
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->approvalRequest->loadMissing(['employee', 'outsourcedEmployee']);
        $assigneeName = $this->resolveAssigneeName($request);

        return (new MailMessage)
            ->subject('Shift Roster Rejected')
            ->view('admin.emails.shift_roster_rejected', [
                'recipientName' => $notifiable->name ?? 'User',
                'assigneeName' => $assigneeName,
                'approverName' => $this->approverName,
                'startDate' => $request->start_date?->format('d M, Y'),
                'endDate' => $request->end_date?->format('d M, Y'),
                'shiftCount' => (int) $request->shift_count,
                'offDayCount' => (int) $request->off_day_count,
                'shiftLabel' => $request->shift_label ?? 'Shift roster',
                'rejectionReason' => trim((string) ($request->rejection_reason ?? '')),
                'actionUrl' => url('/admin/shift-planner'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->approvalRequest->loadMissing(['employee', 'outsourcedEmployee']);
        $assigneeName = $this->resolveAssigneeName($request);

        return [
            'title' => 'Shift Roster Rejected',
            'message' => 'Roster for ' . $assigneeName . ' was rejected by ' . $this->approverName . '.',
            'type' => 'shift_roster_rejected',
            'approval_request_id' => $request->id,
            'assignee_name' => $assigneeName,
            'start_date' => $request->start_date?->toDateString(),
            'end_date' => $request->end_date?->toDateString(),
            'rejection_reason' => $request->rejection_reason,
            'url' => '/admin/shift-planner',
        ];
    }

    private function resolveAssigneeName(ShiftRosterApprovalRequest $request): string
    {
        if ($request->employee) {
            return trim((string) ($request->employee->full_name ?? $request->employee->first_name ?? 'Employee'));
        }

        if ($request->outsourcedEmployee) {
            return trim((string) ($request->outsourcedEmployee->full_name ?? 'Third-party employee'));
        }

        return 'Employee';
    }
}
