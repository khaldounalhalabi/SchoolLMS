<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationWebController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(DatabaseNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($notification);
        $notification->markAsRead();

        return redirect()->to($notification->data['url'] ?? route('notifications.index'));
    }

    public function markAllRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', __('All notifications marked as read.'));
    }

    private function authorizeNotification(DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === Auth::user()->getMorphClass()
                && (int) $notification->notifiable_id === (int) Auth::id(),
            404,
        );
    }
}
