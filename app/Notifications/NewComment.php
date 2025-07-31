<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class NewComment extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TurnitinThreadComment $comment,
        public TurnitinThread $thread,
        public User $commenter
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
            'type' => 'new_comment',
            'title' => 'Komentar Baru',
            'message' => "{$this->commenter->name} menambahkan komentar pada thread: {$this->thread->name}",
            'thread_id' => $this->thread->id,
            'thread_name' => $this->thread->name,
            'comment_id' => $this->comment->id,
            'commenter_name' => $this->commenter->name,
            'created_at' => now(),
        ];
    }
}
