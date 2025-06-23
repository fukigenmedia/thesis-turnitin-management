<?php

use App\Models\Slider;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public bool $modal = false;
    public mixed $targetDelete = null;

    public function headers(): array
    {
        return [['key' => 'photo', 'label' => 'Foto', 'sortable' => false, 'class' => 'w-96'], ['key' => 'name', 'label' => 'Nama'], ['key' => 'description', 'label' => 'Deskripsi', 'sortable' => false], ['key' => 'status', 'label' => 'Status', 'sortable' => false]];
    }

    public function delete(Slider $slider): void
    {
        if ($slider->photo) {
            $path = str($slider->photo)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }
        $slider->delete();
        $this->modal = false;
        $this->success('Slider berhasil dihapus.');
    }

    public function edit(Slider $slider): void
    {
        $this->redirectRoute('sliders.edit', ['slider' => $slider->id], false, true);
    }

    public function sliders()
    {
        return Slider::query()->when($this->search, fn($q) => $q->where('name', 'like', "%$this->search%"))->orderBy('id', 'desc')->paginate(10);
    }

    public function with(): array
    {
        return [
            'sliders' => $this->sliders(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<x-pages.layout
    page-title="Slider"
    page-subtitle="Kelola slider website."
>
    <x-slot:search>
        <x-mary-input
            class="input"
            placeholder="Cari slider..."
            wire:model.live.debounce="search"
            clearable
            icon="o-magnifying-glass"
        />
    </x-slot:search>
    <x-slot:actions>
        <x-mary-button
            class="btn-primary btn"
            :link="route('sliders.create')"
            icon="o-plus"
            label="Tambah Slider"
            responsive
        />
    </x-slot:actions>
    <x-slot:content>
        <x-mary-table
            :headers="$headers"
            :rows="$sliders"
            with-pagination
        >
            @scope('cell_photo', $slider)
                <img
                    class="rounded"
                    src="{{ $slider->photo ?? '/images/default-slider.png' }}"
                />
            @endscope
            @scope('cell_status', $slider)
                <x-mary-badge
                    :value="$slider->status ? 'Aktif' : 'Nonaktif'"
                    :class="$slider->status ? 'badge-success' : 'badge-soft'"
                />
            @endscope
            @scope('actions', $slider)
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
                        :link="route('sliders.edit', ['slider' => $slider->id])"
                    />
                    <x-mary-menu-item
                        class="text-error"
                        title="Hapus"
                        icon="o-trash"
                        @click="$dispatch('target-delete', { slider: {{ $slider->id }} })"
                        spinner
                    />
                </x-mary-dropdown>
            @endscope
        </x-mary-table>
    </x-slot:content>
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
            $wire.targetDelete = event.slider;
        });
    </script>
@endscript
