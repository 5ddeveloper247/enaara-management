<?php

namespace App\Notifications;

use App\Models\ShiftRosterApprovalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftRosterApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ShiftRosterApprovalRequest $approvalRequest
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->approvalRequest->loadMissing([
            'employee.department',
            'outsourcedEmployee',
            'requestedByUser',
        ]);

        $assigneeName = $this->resolveAssigneeName($request);
        $requestedBy = $request->requestedByUser?->name ?? 'A roster planner';
        $departmentName = $request->employee?->department?->name ?? '-';

        return (new MailMessage)
            ->subject('Shift Roster Approval Required')
            ->view('admin.emails.shift_roster_approval_required', [
                'recipientName' => $notifiable->name ?? 'Manager',
                'assigneeName' => $assigneeName,
                'requestedByName' => $requestedBy,
                'departmentName' => $departmentName,
                'startDate' => $request->start_date?->format('d M, Y'),
                'endDate' => $request->end_date?->format('d M, Y'),
                'shiftCount' => (int) $request->shift_count,
                'offDayCount' => (int) $request->off_day_count,
                'shiftLabel' => $request->shift_label ?? 'Shift roster',
                'actionUrl' => url('/admin/dashboard?roster_approval=' . $request->id),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->approvalRequest->loadMissing(['employee', 'outsourcedEmployee', 'requestedByUser']);
        $assigneeName = $this->resolveAssigneeName($request);

        return [
            'title' => 'Shift Roster Approval Required',
            'message' => $assigneeName . ' has a pending shift roster submitted by '
                . ($request->requestedByUser?->name ?? 'a planner') . '.',
            'type' => 'shift_roster_approval',
            'approval_request_id' => $request->id,
            'assignee_name' => $assigneeName,
            'start_date' => $request->start_date?->toDateString(),
            'end_date' => $request->end_date?->toDateString(),
            'url' => '/admin/dashboard?roster_approval=' . $request->id,
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
