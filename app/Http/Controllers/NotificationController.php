<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read and redirect to the target URL.
     */
    public function markAsReadAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data ?? [];
        $targetUrl = $data['url'] ?? '/admin/leave-request';

        if (($data['type'] ?? '') === 'shift_roster_approval' && ! empty($data['approval_request_id'])) {
            $targetUrl = url('/admin/shift-planner?roster_approval=' . (int) $data['approval_request_id']);
        }

        return redirect($targetUrl);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
