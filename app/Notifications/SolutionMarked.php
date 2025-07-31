<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class SolutionMarked extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TurnitinThreadComment $comment,
        public TurnitinThread $thread,
        public User $marker
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
            'type' => 'solution_marked',
            'title' => 'Jawaban Ditandai Sebagai Solusi',
            'message' => "{$this->marker->name} menandai jawaban sebagai solusi pada thread: {$this->thread->name}",
            'thread_id' => $this->thread->id,
            'thread_name' => $this->thread->name,
            'comment_id' => $this->comment->id,
            'marker_name' => $this->marker->name,
            'created_at' => now(),
        ];
    }
}
