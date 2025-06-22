<?php

use App\Models\User;
use App\Enums\UserStatus;
use App\Enums\UserRole;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\ClearsFilters;

new class extends Component {
    use Toast;
    use WithPagination;
    use ClearsFilters;

    public string $search = '';

    public ?int $status = null;

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $modal = false;

    public mixed $targetDelete = null;

    public function headers(): array
    {
        return [['key' => 'avatar', 'label' => 'Avatar', 'sortable' => false, 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'email', 'label' => 'Email', 'sortable' => false], ['key' => 'role', 'label' => 'Role', 'sortable' => false]];
    }

    public function delete(User $user): void
    {
        if ($user->avatar) {
            $path = str($user->avatar)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }

        $user->delete();

        $this->modal = false;

        $this->success('Pengguna berhasil dihapus.');
    }

    public function edit(User $user): void
    {
        $this->redirectRoute('users.edit', ['user' => $user->id], false, true);
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))->when($this->status, fn(Builder $q) => $q->where('status', $this->status))->orderBy(...array_values($this->sortBy))->paginate(10);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
            'statusGroup' => UserStatus::all(),
        ];
    }
}; ?>

<x-pages.layout
    page-title="Pengguna"
    page-subtitle="Kelola pengguna sistem Anda."
>
    <x-slot:search>
        <x-mary-input
            class="input"
            placeholder="Cari pengguna..."
            wire:model.live.debounce="search"
            clearable
            icon="o-magnifying-glass"
        />
    </x-slot:search>
    <x-slot:actions>
        <x-mary-button
            class="btn-soft btn"
            label="Filter"
            @click="$wire.drawer=true"
            responsive
            icon="o-funnel"
        />
        <x-mary-button
            class="btn-primary btn"
            :link="route('users.create')"
            icon="o-plus"
            label="Tambah Pengguna"
            responsive
        />
    </x-slot:actions>

    <x-slot:content>
        <x-mary-table
            :headers="$headers"
            :rows="$users"
            :sort-by="$sortBy"
            with-pagination
        >
            @scope('cell_avatar', $user)
                <div
                    class="indicator tooltip"
                    data-tip="{{ $user->status->label() }}"
                >
                    <span @class([
                        'indicator-item status',
                        'status-success' => $user->status === UserStatus::ACTIVE,
                        'status-warning' => $user->status === UserStatus::INACTIVE,
                        'status-error' => $user->status === UserStatus::SUSPENDED,
                    ])></span>
                    <x-mary-avatar
                        class="!w-8 !rounded-lg"
                        image="{{ $user->avatar ?? '/images/empty-user.jpg' }}"
                    />
                </div>
            @endscope

            @scope('cell_role', $user)
                <x-mary-badge
                    class="badge-soft"
                    :value="$user->role->label()"
                >
                </x-mary-badge>
            @endscope

            @scope('actions', $user)
                @if ($user->id !== auth()->id())
                    <x-mary-dropdown>
                        <x-slot:trigger>
                            <x-mary-button
                                class="btn-circle"
                                icon="o-ellipsis-horizontal"
                            />
                        </x-slot:trigger>

                        <x-mary-menu-item
                            title="Edit"
                            icon="o-pencil"
                            :link="route('users.edit', ['user' => $user->id])"
                        />
                        <x-mary-menu-item
                            class="text-error"
                            title="Hapus"
                            icon="o-trash"
                            @click="$dispatch('target-delete', { user: {{ $user->id }} })"
                            spinner
                        />
                    </x-mary-dropdown>
                @endif
            @endscope
        </x-mary-table>
    </x-slot:content>

    <x-mary-drawer
        class="lg:w-1/3"
        wire:model="drawer"
        :title="__('Filters')"
        right
        separator
        with-close-button
    >
        <x-mary-group
            class="[&:checked]:!btn-primary"
            :label="__('Status')"
            wire:model.live="status"
            :options="$statusGroup"
        />

        <x-slot:actions>
            <x-mary-button
                class="btn-soft"
                :label="__('Reset')"
                icon="o-x-mark"
                wire:click="clear"
                spinner
            />
            <x-mary-button
                class="btn-primary"
                :label="__('Done')"
                icon="o-check"
                @click="$wire.drawer = false"
            />
        </x-slot:actions>
    </x-mary-drawer>

    <x-mary-modal
        class="backdrop-blur"
        title="Hapus Data"
        wire:model="modal"
        subtitle="Apakah Anda yakin menghapus data ini?"
    >
        <x-slot:actions>
            <x-mary-button
                class="btn-error"
                label="Ya"
                wire:click="delete($wire.targetDelete)"
                spinner="delete"
            />
            <x-mary-button
                class="btn-soft"
                label="Batal"
                @click="$wire.modal = false"
            />
        </x-slot:actions>
    </x-mary-modal>
</x-pages.layout>

@script
    <script>
        $wire.on('target-delete', (event) => {
            $wire.modal = true;
            $wire.targetDelete = event.user;
        });
    </script>
@endscript
