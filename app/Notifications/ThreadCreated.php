<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TurnitinThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class ThreadCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TurnitinThread $thread,
        public User $creator
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'thread_created',
            'title' => 'Thread Baru Dibuat',
            'message' => "Anda ditag dalam thread baru: {$this->thread->name}",
            'thread_id' => $this->thread->id,
            'thread_name' => $this->thread->name,
            'creator_name' => $this->creator->name,
            'created_at' => now(),
        ];
    }
}
