<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('read')
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $user = $request->user();

        // Verify the notification belongs to the user
        if ($notification->user_id !== $user->id) {
            abort(403, 'You are not authorized to update this notification.');
        }

        $notification->markAsRead();

        return back()->with('status', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->notifications()
            ->where('read', false)
            ->update(['read' => true]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
