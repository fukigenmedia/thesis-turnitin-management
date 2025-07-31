<?php

declare(strict_types=1);

namespace App\Services;

final class PushNotificationService
{
    /**
     * Send push notification to browser
     */
    public function sendPushNotification(
        string $title,
        string $message,
        ?string $icon = null,
        ?string $url = null,
        ?array $actions = null
    ): void {
        $notification = [
            'title' => $title,
            'message' => $message,
            'icon' => $icon ?? '/images/notification-icon.png',
            'url' => $url,
            'actions' => $actions,
            'timestamp' => now()->toISOString(),
        ];

        // Dispatch browser event for immediate push notification
        $this->dispatchBrowserEvent($notification);
    }

    /**
     * Create notification data for thread created
     */
    public function createThreadNotification(string $threadName, string $creatorName): array
    {
        return [
            'title' => 'Thread Baru Dibuat',
            'message' => "Anda ditag dalam thread baru: {$threadName} oleh {$creatorName}",
            'icon' => '/images/thread-icon.png',
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'Lihat Thread',
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Tutup',
                ],
            ],
        ];
    }

    /**
     * Create notification data for new comment
     */
    public function createCommentNotification(string $threadName, string $commenterName): array
    {
        return [
            'title' => 'Komentar Baru',
            'message' => "{$commenterName} menambahkan komentar pada thread: {$threadName}",
            'icon' => '/images/comment-icon.png',
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'Lihat Komentar',
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Tutup',
                ],
            ],
        ];
    }

    /**
     * Create notification data for solution marked
     */
    public function createSolutionNotification(string $threadName, string $markerName): array
    {
        return [
            'title' => 'Jawaban Ditandai Sebagai Solusi',
            'message' => "{$markerName} menandai jawaban sebagai solusi pada thread: {$threadName}",
            'icon' => '/images/solution-icon.png',
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'Lihat Solusi',
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Tutup',
                ],
            ],
        ];
    }

    /**
     * Create notification data for status changed
     */
    public function createStatusNotification(
        string $threadName,
        string $changerName,
        string $oldStatus,
        string $newStatus
    ): array {
        return [
            'title' => 'Status Thread Berubah',
            'message' => "{$changerName} mengubah status thread '{$threadName}' dari {$oldStatus} ke {$newStatus}",
            'icon' => '/images/status-icon.png',
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'Lihat Thread',
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Tutup',
                ],
            ],
        ];
    }

    /**
     * Dispatch browser event for push notification
     */
    private function dispatchBrowserEvent(array $notification): void
    {
        // This will be handled by JavaScript on the frontend
        // We'll use Livewire events to trigger the push notification
        event('push-notification', $notification);
    }
}
