<div
    class="relative"
    x-data="{ isOpen: @entangle('isOpen') }"
    @click.away="isOpen = false"
    wire:poll.30s="loadNotifications"
>
    <!-- Notification Bell -->
    <button
        class="relative flex items-center justify-center p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
        @click="isOpen = !isOpen"
    >
        <x-mary-icon
            class="h-6 w-6"
            name="s-bell"
        />
        @if ($unreadCount > 0)
            <x-mary-badge
                class="badge-error badge-sm absolute -right-1 -top-1 min-h-5 min-w-5"
                :value="$unreadCount > 99 ? '99+' : $unreadCount"
            />
        @endif
    </button>

    <!-- Dropdown -->
    <div
        class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md border border-gray-200 bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        style="display: none;"
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
    >
        <div class="py-1">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2">
                <h3 class="text-sm font-medium text-gray-900">Notifikasi</h3>
                @if ($unreadCount > 0)
                    <button
                        class="text-xs text-blue-600 hover:text-blue-800"
                        wire:click="markAllAsRead"
                    >
                        Tandai semua dibaca
                    </button>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <div
                        class="{{ is_null($notification['read_at']) ? 'bg-blue-50 border-l-4 border-blue-400' : '' }} flex cursor-pointer items-start px-4 py-3 hover:bg-gray-50"
                        wire:click="goToThread({{ $notification['thread_id'] }}, '{{ $notification['id'] }}')"
                    >
                        <div class="flex-shrink-0">
                            @if ($notification['type'] === 'thread_created')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                                    <x-mary-icon
                                        class="h-4 w-4 text-green-600"
                                        name="o-plus"
                                    />
                                </div>
                            @elseif ($notification['type'] === 'new_comment')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                    <x-mary-icon
                                        class="h-4 w-4 text-blue-600"
                                        name="o-chat-bubble-left-right"
                                    />
                                </div>
                            @elseif ($notification['type'] === 'solution_marked')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-100">
                                    <x-mary-icon
                                        class="h-4 w-4 text-yellow-600"
                                        name="o-star"
                                    />
                                </div>
                            @elseif ($notification['type'] === 'status_changed')
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-100">
                                    <x-mary-icon
                                        class="h-4 w-4 text-purple-600"
                                        name="o-arrow-path"
                                    />
                                </div>
                            @endif
                        </div>

                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification['title'] }}
                            </p>
                            <p class="truncate text-sm text-gray-500">
                                {{ $notification['message'] }}
                            </p>
                            <p class="mt-1 text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                            </p>
                        </div>

                        @if (is_null($notification['read_at']))
                            <div class="ml-2 flex-shrink-0">
                                <div class="h-2 w-2 rounded-full bg-blue-600"></div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <x-mary-icon
                            class="mx-auto h-12 w-12 text-gray-300"
                            name="o-bell-slash"
                        />
                        <p class="mt-2 text-sm text-gray-500">Tidak ada notifikasi</p>
                    </div>
                @endforelse
            </div>

            @if (count($notifications) > 0)
                <!-- Footer -->
                <div class="border-t border-gray-200 px-4 py-2">
                    <a
                        class="block text-center text-sm text-blue-600 hover:text-blue-800"
                        href="{{ route('notifications.index') }}"
                    >
                        Lihat semua notifikasi
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
