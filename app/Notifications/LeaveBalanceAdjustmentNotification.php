<?php

namespace App\Notifications;

use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveBalanceAdjustment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveBalanceAdjustmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LeaveBalanceAdjustment $adjustment,
        public EmployeeLeaveQuota $quota,
        public float $previousRemaining,
        public string $actorName,
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->adjustment->loadMissing('leaveType');
        $leaveTypeName = $this->adjustment->leaveType?->name ?? 'Leave';
        $newRemaining = (float) $this->quota->remaining_balance;

        return (new MailMessage)
            ->subject('Leave Balance Updated – ' . $leaveTypeName)
            ->view('admin.emails.leave_balance_adjustment', [
                'recipientName' => $notifiable->name ?? $notifiable->full_name ?? 'Employee',
                'actorName' => $this->actorName,
                'leaveType' => $leaveTypeName,
                'adjustmentType' => $this->adjustment->adjustment_type,
                'adjustmentLabel' => $this->adjustmentLabel(),
                'days' => $this->formatDays((float) $this->adjustment->days),
                'reason' => $this->adjustment->reason,
                'previousRemaining' => $this->formatDays($this->previousRemaining),
                'newRemaining' => $this->formatDays($newRemaining),
                'year' => (int) $this->quota->year,
                'actionUrl' => url('/admin/my-leaves'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $this->adjustment->loadMissing('leaveType');
        $leaveTypeName = $this->adjustment->leaveType?->name ?? 'Leave';
        $newRemaining = (float) $this->quota->remaining_balance;
        $days = $this->formatDays((float) $this->adjustment->days);

        return [
            'title' => 'Leave Balance Adjusted',
            'message' => "Your {$leaveTypeName} balance was {$this->adjustmentLabel()} by {$days} day(s). Remaining balance is now {$this->formatDays($newRemaining)} day(s).",
            'type' => 'leave_balance_adjustment',
            'adjustment_id' => $this->adjustment->id,
            'employee_id' => $this->adjustment->employee_id,
            'leave_type_id' => $this->adjustment->leave_type_id,
            'leave_type_name' => $leaveTypeName,
            'adjustment_type' => $this->adjustment->adjustment_type,
            'days' => (float) $this->adjustment->days,
            'previous_remaining' => $this->previousRemaining,
            'new_remaining' => $newRemaining,
            'actor_name' => $this->actorName,
            'url' => '/admin/my-leaves',
        ];
    }

    private function adjustmentLabel(): string
    {
        return $this->adjustment->adjustment_type === 'add' ? 'increased' : 'decreased';
    }

    private function formatDays(float $days): string
    {
        if (fmod($days, 1.0) === 0.0) {
            return (string) (int) $days;
        }

        return rtrim(rtrim(number_format($days, 2, '.', ''), '0'), '.');
    }
}
