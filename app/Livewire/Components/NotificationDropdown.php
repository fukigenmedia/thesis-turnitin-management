<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

final class NotificationDropdown extends Component
{
    public int $unreadCount = 0;

    public $notifications = [];

    public bool $isOpen = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'],
                    'title' => $notification->data['title'],
                    'message' => $notification->data['message'],
                    'thread_id' => $notification->data['thread_id'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            })
            ->toArray();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function goToThread($threadId, $notificationId)
    {
        $this->markAsRead($notificationId);

        return redirect()->route('turnitin-threads.show', ['thread' => $threadId]);
    }

    public function toggleDropdown()
    {
        $this->isOpen = ! $this->isOpen;
    }

    #[On('notification-updated')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.components.notification-dropdown');
    }
}
