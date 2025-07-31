<?php

use App\Models\TurnitinThread;
use App\Models\User;
use App\Enums\TurnitinThreadStatus;
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

    public array $sortBy = ['column' => 'datetime', 'direction' => 'desc'];

    public bool $modal = false;

    public mixed $targetDelete = null;

    public function headers(): array
    {
        return [['key' => 'datetime', 'label' => 'Tanggal', 'class' => 'w-32'], ['key' => 'name', 'label' => 'Nama Thread'], ['key' => 'student_name', 'label' => 'Mahasiswa', 'sortable' => false], ['key' => 'lecturer_name', 'label' => 'Dosen', 'sortable' => false], ['key' => 'status', 'label' => 'Status', 'sortable' => false, 'class' => 'w-32']];
    }

    public function delete(TurnitinThread $thread): void
    {
        if ($thread->file_name) {
            \Storage::disk('public')->delete('turnitin-threads/' . $thread->file_name);
        }

        $thread->delete();

        $this->modal = false;

        $this->success('Thread berhasil dihapus.');
    }

    public function edit(TurnitinThread $thread): void
    {
        $this->redirectRoute('turnitin-threads.edit', ['thread' => $thread->id], false, true);
    }

    public function show(TurnitinThread $thread): void
    {
        $this->redirectRoute('turnitin-threads.show', ['thread' => $thread->id], false, true);
    }

    public function threads(): LengthAwarePaginator
    {
        $query = TurnitinThread::query()
            ->with(['student', 'lecturer'])
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $subQ) {
                    $subQ
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhereHas('student', fn(Builder $studentQ) => $studentQ->where('name', 'like', "%{$this->search}%"))
                        ->orWhereHas('lecturer', fn(Builder $lecturerQ) => $lecturerQ->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->status !== null, fn(Builder $q) => $q->where('status', $this->status));

        // Role-based filtering
        $user = auth()->user();
        if ($user->role === UserRole::STUDENT) {
            $query->where('student_id', $user->id);
        } elseif ($user->role === UserRole::LECTURE) {
            $query->where('lecture_id', $user->id);
        }

        return $query->orderBy(...array_values($this->sortBy))->paginate(10);
    }

    public function with(): array
    {
        return [
            'threads' => $this->threads(),
            'headers' => $this->headers(),
            'statusGroup' => TurnitinThreadStatus::all(),
            'canCreate' => auth()->user()->role !== UserRole::ADMIN,
        ];
    }
}; ?>

<x-pages.layout
    page-title="Turnitin Threads"
    page-subtitle="Kelola thread turnitin dalam sistem."
>
    <x-slot:search>
        <x-mary-input
            class="input"
            placeholder="Cari thread..."
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
        @if ($canCreate)
            <x-mary-button
                class="btn-primary btn"
                :link="route('turnitin-threads.create')"
                icon="o-plus"
                label="Tambah Thread"
                responsive
            />
        @endif
    </x-slot:actions>

    <x-slot:content>
        <x-mary-table
            :headers="$headers"
            :rows="$threads"
            :sort-by="$sortBy"
            with-pagination
        >
            @scope('cell_datetime', $thread)
                <div class="text-sm">
                    {{ $thread->datetime->format('d/m/Y') }}<br>
                    <span class="text-gray-500">{{ $thread->datetime->format('H:i') }}</span>
                </div>
            @endscope

            @scope('cell_name', $thread)
                <div>
                    <div class="font-medium">{{ $thread->name }}</div>
                    @if ($thread->description)
                        <div class="max-w-xs truncate text-sm text-gray-500">
                            {{ Str::limit($thread->description, 50) }}
                        </div>
                    @endif
                    @if ($thread->file_original_name)
                        <div class="mt-1 text-xs text-blue-600">
                            <x-mary-icon
                                class="inline h-3 w-3"
                                name="o-document"
                            />
                            {{ $thread->file_original_name }}
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_student_name', $thread)
                <div class="flex items-center gap-2">
                    <x-mary-avatar
                        class="!w-6 !rounded-lg"
                        image="{{ $thread->student->avatar ?? '/images/empty-user.jpg' }}"
                    />
                    <span class="text-sm">{{ $thread->student->name }}</span>
                </div>
            @endscope

            @scope('cell_lecturer_name', $thread)
                <div class="flex items-center gap-2">
                    <x-mary-avatar
                        class="!w-6 !rounded-lg"
                        image="{{ $thread->lecturer->avatar ?? '/images/empty-user.jpg' }}"
                    />
                    <span class="text-sm">{{ $thread->lecturer->name }}</span>
                </div>
            @endscope

            @scope('cell_status', $thread)
                <x-mary-badge
                    @class([
                        'badge-soft' => $thread->status === TurnitinThreadStatus::OPEN,
                        'badge-warning' => $thread->status === TurnitinThreadStatus::PROCESSING,
                        'badge-success' => $thread->status === TurnitinThreadStatus::DONE,
                    ])
                    :value="$thread->status->label()"
                />
            @endscope

            @scope('actions', $thread)
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button
                            class="btn-circle"
                            icon="o-ellipsis-horizontal"
                        />
                    </x-slot:trigger>

                    <x-mary-menu-item
                        title="Lihat"
                        icon="o-eye"
                        wire:click="show({{ $thread->id }})"
                    />

                    @if (auth()->user()->role === UserRole::STUDENT && auth()->id() === $thread->student_id)
                        <x-mary-menu-item
                            title="Edit"
                            icon="o-pencil"
                            wire:click="edit({{ $thread->id }})"
                        />
                    @endif

                    @if (auth()->user()->role === UserRole::ADMIN || auth()->id() === $thread->student_id)
                        <x-mary-menu-item
                            class="text-error"
                            title="Hapus"
                            icon="o-trash"
                            @click="$dispatch('target-delete', { thread: {{ $thread->id }} })"
                            spinner
                        />
                    @endif
                </x-mary-dropdown>
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
        title="Hapus Thread"
        wire:model="modal"
        subtitle="Apakah Anda yakin menghapus thread ini?"
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
            $wire.targetDelete = event.thread;
        });
    </script>
@endscript
