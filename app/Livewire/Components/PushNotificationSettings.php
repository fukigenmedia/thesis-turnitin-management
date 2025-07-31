<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class PushNotificationSettings extends Component
{
    public bool $isEnabled = false;

    public bool $threadCreated = true;

    public bool $newComment = true;

    public bool $solutionMarked = true;

    public bool $statusChanged = true;

    public string $permission = 'default';

    public function mount()
    {
        $user = Auth::user();

        // Load user preferences from database or session
        $this->isEnabled = $user->getSetting('push_notifications_enabled', false);
        $this->threadCreated = $user->getSetting('push_thread_created', true);
        $this->newComment = $user->getSetting('push_new_comment', true);
        $this->solutionMarked = $user->getSetting('push_solution_marked', true);
        $this->statusChanged = $user->getSetting('push_status_changed', true);
    }

    public function toggleNotifications()
    {
        $this->isEnabled = ! $this->isEnabled;
        $this->saveSettings();

        if ($this->isEnabled) {
            $this->dispatch('request-notification-permission');
        } else {
            $this->dispatch('disable-notifications');
        }
    }

    public function updateSettings()
    {
        $this->saveSettings();
        $this->dispatch('notification-settings-updated', [
            'threadCreated' => $this->threadCreated,
            'newComment' => $this->newComment,
            'solutionMarked' => $this->solutionMarked,
            'statusChanged' => $this->statusChanged,
        ]);

        session()->flash('message', 'Pengaturan notifikasi berhasil disimpan.');
    }

    public function testNotification()
    {
        $this->dispatch('show-push-notification', [
            'title' => 'Test Notifikasi',
            'message' => 'Ini adalah test notifikasi push browser.',
            'icon' => '/images/notification-icon.png',
            'url' => '/notifications',
        ]);
    }

    public function render()
    {
        return view('livewire.components.push-notification-settings');
    }

    private function saveSettings()
    {
        $user = Auth::user();

        // Save to user settings (you might want to create a settings table)
        $settings = [
            'push_notifications_enabled' => $this->isEnabled,
            'push_thread_created' => $this->threadCreated,
            'push_new_comment' => $this->newComment,
            'push_solution_marked' => $this->solutionMarked,
            'push_status_changed' => $this->statusChanged,
        ];

        foreach ($settings as $key => $value) {
            $user->setSetting($key, $value);
        }
    }
}
