<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

final class NotificationIndex extends Component
{
    use WithPagination;

    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function goToThread($threadId, $notificationId = null)
    {
        if ($notificationId) {
            $this->markAsRead($notificationId);
        }

        return redirect()->route('turnitin-threads.show', ['thread' => $threadId]);
    }

    public function render()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(20);

        return view('livewire.pages.notification-index', [
            'notifications' => $notifications,
        ]);
    }
}
