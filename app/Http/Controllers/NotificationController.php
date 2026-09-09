<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->ensureNotificationBelongsToUser($request, $notification);

        $notification->markAsRead();

        return back()->with('success', 'Notification marquée comme lue.');
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->ensureNotificationBelongsToUser($request, $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return redirect()->to($this->redirectUrlFor($notification));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->ensureNotificationBelongsToUser($request, $notification);

        $notification->delete();

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Notification supprimée.');
    }

    private function ensureNotificationBelongsToUser(Request $request, DatabaseNotification $notification): void
    {
        $user = $request->user();

        abort_unless(
            $notification->notifiable_type === $user::class
            && (string) $notification->notifiable_id === (string) $user->getKey(),
            404
        );
    }

    private function redirectUrlFor(DatabaseNotification $notification): string
    {
        $url = $notification->data['url'] ?? null;

        if (! is_string($url) || $url === '') {
            return route('notifications.index');
        }

        if (str_starts_with($url, url('/'))) {
            return $url;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        return route('notifications.index');
    }
}
