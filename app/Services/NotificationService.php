<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TurnitinThreadStatus;
use App\Events\NotificationSent;
use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\SolutionMarked;
use App\Notifications\ThreadCreated;
use App\Notifications\ThreadStatusChanged;
use Illuminate\Support\Facades\Notification;

final class NotificationService
{
    public function __construct(
        private PushNotificationService $pushNotificationService
    ) {}

    /**
     * Send notification when a thread is created
     */
    public function sendThreadCreatedNotification(TurnitinThread $thread, User $creator): void
    {
        // Notify the lecturer assigned to the thread
        if ($thread->lecturer && $thread->lecturer->id !== $creator->id) {
            // Check if user wants to receive this type of notification
            if ($thread->lecturer->thread_created_notifications) {
                $thread->lecturer->notify(new ThreadCreated($thread, $creator));
                $this->dispatchNotificationEvent($thread->lecturer->id);

                // Send push notification if enabled
                $this->sendPushNotificationIfEnabled(
                    $thread->lecturer,
                    'thread_created',
                    'Thread Baru Dibuat',
                    "Anda ditag dalam thread baru: {$thread->name} oleh {$creator->name}",
                    route('turnitin-threads.show', $thread->id)
                );
            }
        }
    }

    /**
     * Send notification when a new comment is added
     */
    public function sendNewCommentNotification(TurnitinThreadComment $comment, User $commenter): void
    {
        $thread = $comment->thread;
        $usersToNotify = collect();

        // Add student if commenter is not the student
        if ($thread->student && $thread->student->id !== $commenter->id) {
            $usersToNotify->push($thread->student);
        }

        // Add lecturer if commenter is not the lecturer
        if ($thread->lecturer && $thread->lecturer->id !== $commenter->id) {
            $usersToNotify->push($thread->lecturer);
        }

        // Add other commenters (excluding current commenter)
        $otherCommenters = $thread->comments()
            ->with('user')
            ->where('user_id', '!=', $commenter->id)
            ->get()
            ->pluck('user')
            ->unique('id')
            ->reject(function ($user) use ($usersToNotify) {
                return $usersToNotify->contains('id', $user->id);
            });

        $usersToNotify = $usersToNotify->merge($otherCommenters);

        // Send notifications
        foreach ($usersToNotify as $user) {
            // Check if user wants to receive this type of notification
            if ($user->new_comment_notifications) {
                $user->notify(new NewComment($comment, $thread, $commenter));
                $this->dispatchNotificationEvent($user->id);

                // Send push notification if enabled
                $this->sendPushNotificationIfEnabled(
                    $user,
                    'new_comment',
                    'Komentar Baru',
                    "{$commenter->name} menambahkan komentar pada thread: {$thread->name}",
                    route('turnitin-threads.show', $thread->id)
                );
            }
        }
    }

    /**
     * Send notification when a comment is marked as solution
     */
    public function sendSolutionMarkedNotification(
        TurnitinThreadComment $comment,
        TurnitinThread $thread,
        User $marker
    ): void {
        $usersToNotify = collect();

        // Notify the comment author (if not the marker)
        if ($comment->user && $comment->user->id !== $marker->id) {
            $usersToNotify->push($comment->user);
        }

        // Notify the student (if not the marker and not the comment author)
        if ($thread->student &&
            $thread->student->id !== $marker->id &&
            $thread->student->id !== $comment->user_id) {
            $usersToNotify->push($thread->student);
        }

        // Send notifications
        foreach ($usersToNotify as $user) {
            // Check if user wants to receive this type of notification
            if ($user->solution_marked_notifications) {
                $user->notify(new SolutionMarked($comment, $thread, $marker));
                $this->dispatchNotificationEvent($user->id);

                // Send push notification if enabled
                $this->sendPushNotificationIfEnabled(
                    $user,
                    'solution_marked',
                    'Jawaban Ditandai Sebagai Solusi',
                    "{$marker->name} menandai jawaban sebagai solusi pada thread: {$thread->name}",
                    route('turnitin-threads.show', $thread->id)
                );
            }
        }
    }

    /**
     * Send notification when thread status is changed
     */
    public function sendThreadStatusChangedNotification(
        TurnitinThread $thread,
        TurnitinThreadStatus $oldStatus,
        TurnitinThreadStatus $newStatus,
        User $changer
    ): void {
        $usersToNotify = collect();

        // Notify the student (if not the changer)
        if ($thread->student && $thread->student->id !== $changer->id) {
            $usersToNotify->push($thread->student);
        }

        // Notify the lecturer (if not the changer)
        if ($thread->lecturer && $thread->lecturer->id !== $changer->id) {
            $usersToNotify->push($thread->lecturer);
        }

        // Send notifications
        foreach ($usersToNotify as $user) {
            // Check if user wants to receive this type of notification
            if ($user->thread_status_notifications) {
                $user->notify(new ThreadStatusChanged($thread, $oldStatus, $newStatus, $changer));
                $this->dispatchNotificationEvent($user->id);

                // Send push notification if enabled
                $this->sendPushNotificationIfEnabled(
                    $user,
                    'status_changed',
                    'Status Thread Berubah',
                    "{$changer->name} mengubah status thread '{$thread->name}' " .
                    "dari {$oldStatus->label()} ke {$newStatus->label()}",
                    route('turnitin-threads.show', $thread->id)
                );
            }
        }
    }

    /**
     * Send push notification if user has enabled it
     */
    private function sendPushNotificationIfEnabled(
        User $user,
        string $type,
        string $title,
        string $message,
        string $url
    ): void {
        // Check if user has push notifications enabled
        if ($user->push_notifications_enabled) {
            event('push-notification', [
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'type' => $type,
            ]);
        }
    }

    /**
     * Dispatch notification event for real-time updates
     */
    private function dispatchNotificationEvent(int $userId): void
    {
        event(new NotificationSent($userId));
    }
}
