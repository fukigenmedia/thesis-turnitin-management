<?php

use App\Livewire\Pages\NotificationIndex;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use \Livewire\WithPagination;

    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function goToThread($threadId, $notificationId = null)
    {
        if ($notificationId) {
            $this->markAsRead($notificationId);
        }

        return redirect()->route('turnitin-threads.show', ['thread' => $threadId]);
    }

    public function getNotificationsProperty()
    {
        return Auth::user()->notifications()->latest()->paginate(20);
    }
}; ?>

<x-pages.layout
    page-title="Notifikasi"
    page-subtitle="Kelola semua notifikasi Anda."
>
    <x-slot:actions>
        @if (Auth::user()->unreadNotifications()->count() > 0)
            <x-mary-button
                class="btn-soft btn"
                label="Tandai Semua Dibaca"
                wire:click="markAllAsRead"
                icon="o-check"
                responsive
            />
        @endif
    </x-slot:actions>

    <x-slot:content>
        <x-mary-card>
            <div class="space-y-4">
                @forelse ($this->notifications as $notification)
                    <div
                        class="{{ is_null($notification->read_at) ? 'bg-blue-50 border-l-4 border-blue-400' : 'border-gray-200' }} flex cursor-pointer items-start rounded-lg border p-4 hover:bg-gray-50"
                        wire:click="goToThread({{ $notification->data['thread_id'] ?? 0 }}, '{{ $notification->id }}')"
                    >
                        <div class="flex-shrink-0">
                            @if ($notification->data['type'] === 'thread_created')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                    <x-mary-icon
                                        class="h-5 w-5 text-green-600"
                                        name="o-plus"
                                    />
                                </div>
                            @elseif ($notification->data['type'] === 'new_comment')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                    <x-mary-icon
                                        class="h-5 w-5 text-blue-600"
                                        name="o-chat-bubble-left-right"
                                    />
                                </div>
                            @elseif ($notification->data['type'] === 'solution_marked')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100">
                                    <x-mary-icon
                                        class="h-5 w-5 text-yellow-600"
                                        name="o-star"
                                    />
                                </div>
                            @elseif ($notification->data['type'] === 'status_changed')
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                                    <x-mary-icon
                                        class="h-5 w-5 text-purple-600"
                                        name="o-arrow-path"
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="ml-4 min-w-0 flex-1">
                            <div class="mb-1 flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $notification->data['title'] }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ $notification->data['message'] }}
                            </p>
                            @if (isset($notification->data['thread_name']))
                                <p class="mt-1 text-xs text-gray-500">
                                    Thread: {{ $notification->data['thread_name'] }}
                                </p>
                            @endif
                        </div>

                        @if (is_null($notification->read_at))
                            <div class="ml-4 flex-shrink-0">
                                <div class="h-3 w-3 rounded-full bg-blue-600"></div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <x-mary-icon
                            class="mx-auto h-16 w-16 text-gray-300"
                            name="o-bell-slash"
                        />
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak ada notifikasi</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Anda akan menerima notifikasi ketika ada aktivitas baru pada thread Anda.
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($this->notifications->hasPages())
                <div class="mt-6">
                    {{ $this->notifications->links() }}
                </div>
            @endif
        </x-mary-card>
    </x-slot:content>
</x-pages.layout>
