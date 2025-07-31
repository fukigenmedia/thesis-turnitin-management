<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\TurnitinThreadStatus;
use App\Models\TurnitinThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class ThreadStatusChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TurnitinThread $thread,
        public TurnitinThreadStatus $oldStatus,
        public TurnitinThreadStatus $newStatus,
        public User $changer
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
            'type' => 'status_changed',
            'title' => 'Status Thread Berubah',
            'message' => "{$this->changer->name} mengubah status thread '{$this->thread->name}' " .
                        "dari {$this->oldStatus->label()} ke {$this->newStatus->label()}",
            'thread_id' => $this->thread->id,
            'thread_name' => $this->thread->name,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'changer_name' => $this->changer->name,
            'created_at' => now(),
        ];
    }
}
