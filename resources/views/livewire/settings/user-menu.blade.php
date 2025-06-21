<?php

use Livewire\Volt\Component;
use App\Livewire\Actions\Logout;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component {
    public User $user;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    #[On('profile-updated')]
    public function onProfileUpdated(User $user): void
    {
        $this->user = $user;
    }
}; ?>
<div>
    <x-mary-dropdown right>
        <x-slot:trigger
            class="cursor-pointer transition-all hover:opacity-80"
        >
            <x-mary-avatar
                class="!text-base-content !bg-base-300 !w-10 overflow-hidden"
                :image="$user->avatar ?? '/images/empty-user.jpg'"
            >
                <x-slot:title
                    class="max-w-[150px] truncate text-sm font-semibold"
                >
                    {{ $user->name }}
                </x-slot:title>
                <x-slot:subtitle
                    class="max-w-[150px] truncate text-xs font-light"
                >
                    {{ $user->email }}
                </x-slot:subtitle>
            </x-mary-avatar>
        </x-slot:trigger>
        <x-mary-menu-item
            :title="__('Settings')"
            icon="s-cog-6-tooth"
            :link="route('settings.profile')"
        />
        <x-mary-menu-item
            :title="__('Repository')"
            icon="fab.github"
            link="https://github.com/fukigenmedia/thesis-turnitin-management"
            external
        />
        <x-mary-menu-separator />
        <x-mary-menu-item
            class="text-error"
            :title="__('Log out')"
            wire:click.stop="logout"
            spinner="logout"
            icon="o-power"
        />
    </x-mary-dropdown>
    <style>
        .dropdown {
            width: 100%;
        }
    </style>
</div>
