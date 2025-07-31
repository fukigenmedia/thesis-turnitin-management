<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900">Push Notifications</h3>
        <p class="mt-1 text-sm text-gray-600">
            Kelola pengaturan notifikasi push browser Anda.
        </p>
    </div>

    <!-- Main Toggle -->
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <label class="text-sm font-medium text-gray-900">Aktifkan Push Notifications</label>
            <p class="text-sm text-gray-500">
                Terima notifikasi push langsung di browser Anda
            </p>
        </div>
        <x-mary-toggle
            class="ml-4"
            wire:model.live="isEnabled"
            wire:click="toggleNotifications"
        />
    </div>

    @if ($isEnabled)
        <!-- Permission Status -->
        <div
            class="rounded-lg bg-blue-50 p-4"
            x-data="{ permission: 'default' }"
            x-init="if (window.pushNotificationManager) {
                permission = window.pushNotificationManager.permission;
            }"
        >
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-mary-icon
                        class="h-5 w-5 text-blue-400"
                        name="o-information-circle"
                    />
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800">Status Izin Browser</h4>
                    <p
                        class="mt-1 text-sm text-blue-700"
                        x-text="
                        permission === 'granted' ? 'Izin diberikan - notifikasi akan muncul' :
                        permission === 'denied' ? 'Izin ditolak - aktifkan manual di pengaturan browser' :
                        'Menunggu izin - klik di manapun untuk memberikan izin'
                    "
                    ></p>
                </div>
            </div>
        </div>

        <!-- Notification Types -->
        <div class="space-y-4">
            <h4 class="text-sm font-medium text-gray-900">Jenis Notifikasi</h4>

            <div class="space-y-3">
                <!-- Thread Created -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                            <x-mary-icon
                                class="h-4 w-4 text-green-600"
                                name="o-plus"
                            />
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-900">Thread Baru Dibuat</label>
                            <p class="text-sm text-gray-500">Ketika Anda ditag dalam thread baru</p>
                        </div>
                    </div>
                    <x-mary-toggle
                        wire:model.live="threadCreated"
                        wire:change="updateSettings"
                    />
                </div>

                <!-- New Comment -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                            <x-mary-icon
                                class="h-4 w-4 text-blue-600"
                                name="o-chat-bubble-left-right"
                            />
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-900">Komentar Baru</label>
                            <p class="text-sm text-gray-500">Ketika ada komentar baru pada thread Anda</p>
                        </div>
                    </div>
                    <x-mary-toggle
                        wire:model.live="newComment"
                        wire:change="updateSettings"
                    />
                </div>

                <!-- Solution Marked -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100">
                            <x-mary-icon
                                class="h-4 w-4 text-yellow-600"
                                name="o-star"
                            />
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-900">Jawaban Ditandai Solusi</label>
                            <p class="text-sm text-gray-500">Ketika jawaban Anda ditandai sebagai solusi</p>
                        </div>
                    </div>
                    <x-mary-toggle
                        wire:model.live="solutionMarked"
                        wire:change="updateSettings"
                    />
                </div>

                <!-- Status Changed -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                            <x-mary-icon
                                class="h-4 w-4 text-purple-600"
                                name="o-arrow-path"
                            />
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-900">Status Thread Berubah</label>
                            <p class="text-sm text-gray-500">Ketika status thread Anda diubah</p>
                        </div>
                    </div>
                    <x-mary-toggle
                        wire:model.live="statusChanged"
                        wire:change="updateSettings"
                    />
                </div>
            </div>
        </div>

        <!-- Test Button -->
        <div class="border-t border-gray-200 pt-4">
            <x-mary-button
                class="btn-outline btn-primary"
                label="Test Notifikasi"
                icon="o-bell"
                wire:click="testNotification"
            />
        </div>
    @endif

    @if (session('message'))
        <x-mary-alert
            class="alert-success"
            title="Berhasil!"
            description="{{ session('message') }}"
            icon="o-check-circle"
        />
    @endif
</div>

@script
    <script>
        // Listen for Livewire events
        $wire.on('request-notification-permission', () => {
            if (window.pushNotificationManager) {
                window.pushNotificationManager.requestPermission();
            }
        });

        $wire.on('disable-notifications', () => {
            if (window.pushNotificationManager) {
                window.pushNotificationManager.unsubscribe();
            }
        });

        $wire.on('show-push-notification', (data) => {
            if (window.pushNotificationManager) {
                window.pushNotificationManager.handleNotificationEvent(data);
            }
        });

        $wire.on('notification-settings-updated', (settings) => {
            console.log('Notification settings updated:', settings);
        });
    </script>
@endscript
