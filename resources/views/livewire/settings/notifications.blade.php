<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $pushNotificationsEnabled = false;
    public bool $emailNotificationsEnabled = true;
    public bool $threadCreatedNotifications = true;
    public bool $newCommentNotifications = true;
    public bool $solutionMarkedNotifications = true;
    public bool $threadStatusNotifications = true;

    public function mount(): void
    {
        $user = auth()->user();

        // Load current notification preferences from user settings or database
        $this->pushNotificationsEnabled = $user->push_notifications_enabled ?? false;
        $this->emailNotificationsEnabled = $user->email_notifications_enabled ?? true;
        $this->threadCreatedNotifications = $user->thread_created_notifications ?? true;
        $this->newCommentNotifications = $user->new_comment_notifications ?? true;
        $this->solutionMarkedNotifications = $user->solution_marked_notifications ?? true;
        $this->threadStatusNotifications = $user->thread_status_notifications ?? true;
    }

    public function save(): void
    {
        $user = auth()->user();

        // Save notification preferences to database
        $user->update([
            'push_notifications_enabled' => $this->pushNotificationsEnabled,
            'email_notifications_enabled' => $this->emailNotificationsEnabled,
            'thread_created_notifications' => $this->threadCreatedNotifications,
            'new_comment_notifications' => $this->newCommentNotifications,
            'solution_marked_notifications' => $this->solutionMarkedNotifications,
            'thread_status_notifications' => $this->threadStatusNotifications,
        ]);

        $this->success(__('Notification settings updated successfully.'));
    }

    public function enablePushNotifications(): void
    {
        $this->pushNotificationsEnabled = true;
        $this->dispatch('request-push-permission');
    }

    public function disablePushNotifications(): void
    {
        $this->pushNotificationsEnabled = false;
        $this->dispatch('unsubscribe-push-notifications');
    }
}; ?>

<x-settings.layout
    heading="Pengaturan Notifikasi"
    subheading="Kelola preferensi notifikasi Anda"
>
    <form wire:submit="save">
        <div class="space-y-6">
            <!-- Push Notifications Section -->
            <div>
                <h3 class="text-lg font-medium">Notifikasi Browser</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Terima notifikasi langsung di browser Anda bahkan saat aplikasi tidak terbuka.
                </p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-medium">Aktifkan Notifikasi Browser</label>
                            <p class="text-sm text-gray-500">
                                Izinkan aplikasi mengirim notifikasi push ke browser Anda
                            </p>
                        </div>
                        <x-mary-toggle
                            wire:model.live="pushNotificationsEnabled"
                            @change="$wire.pushNotificationsEnabled ? $wire.enablePushNotifications() : $wire.disablePushNotifications()"
                        />
                    </div>

                    @if (!$pushNotificationsEnabled)
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                            <div class="flex">
                                <x-mary-icon
                                    class="h-5 w-5 text-yellow-400"
                                    name="o-exclamation-triangle"
                                />
                                <div class="ml-3">
                                    <h4 class="text-sm font-medium text-yellow-800">
                                        Notifikasi Browser Dinonaktifkan
                                    </h4>
                                    <p class="mt-1 text-sm text-yellow-700">
                                        Anda tidak akan menerima notifikasi push di browser. Aktifkan untuk mendapatkan
                                        pemberitahuan real-time.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <x-mary-menu-separator />

            <!-- Email Notifications Section -->
            <div>
                <h3 class="text-lg font-medium">Notifikasi Email</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Terima notifikasi melalui email untuk tetap mendapat informasi terbaru.
                </p>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium">Aktifkan Notifikasi Email</label>
                        <p class="text-sm text-gray-500">
                            Kirim notifikasi ke alamat email Anda
                        </p>
                    </div>
                    <x-mary-toggle wire:model="emailNotificationsEnabled" />
                </div>
            </div>

            <x-mary-menu-separator />

            <!-- Notification Types Section -->
            <div>
                <h3 class="text-lg font-medium">Jenis Notifikasi</h3>
                <p class="mb-4 text-sm text-gray-600">
                    Pilih jenis notifikasi yang ingin Anda terima.
                </p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-medium">Thread Baru Dibuat</label>
                            <p class="text-sm text-gray-500">
                                Notifikasi saat dosen ditag dalam thread baru
                            </p>
                        </div>
                        <x-mary-toggle wire:model="threadCreatedNotifications" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-medium">Komentar Baru</label>
                            <p class="text-sm text-gray-500">
                                Notifikasi saat ada komentar baru pada thread Anda
                            </p>
                        </div>
                        <x-mary-toggle wire:model="newCommentNotifications" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-medium">Jawaban Terpilih</label>
                            <p class="text-sm text-gray-500">
                                Notifikasi saat komentar Anda dipilih sebagai solusi
                            </p>
                        </div>
                        <x-mary-toggle wire:model="solutionMarkedNotifications" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <label class="font-medium">Status Thread Berubah</label>
                            <p class="text-sm text-gray-500">
                                Notifikasi saat status thread berubah (buka/tutup/selesai)
                            </p>
                        </div>
                        <x-mary-toggle wire:model="threadStatusNotifications" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <x-mary-button
                class="btn-primary"
                type="submit"
                label="Simpan Pengaturan"
                icon="o-check"
                spinner="save"
            />
        </div>
    </form>
</x-settings.layout>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listen for push notification permission requests
            Livewire.on('request-push-permission', () => {
                if (window.pushNotificationManager) {
                    window.pushNotificationManager.requestPermission()
                        .then(granted => {
                            if (!granted) {
                                // If permission denied, disable the toggle
                                @this.set('pushNotificationsEnabled', false);
                            }
                        });
                }
            });

            // Listen for push notification unsubscribe
            Livewire.on('unsubscribe-push-notifications', () => {
                if (window.pushNotificationManager) {
                    window.pushNotificationManager.unsubscribe();
                }
            });
        });
    </script>
@endpush
