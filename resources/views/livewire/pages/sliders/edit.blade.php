<?php

use Livewire\Volt\Component;
use App\Models\Slider;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

new class extends Component {
    use Toast, WithFileUploads;

    public Slider $slider;

    #[Validate('required|max:100')]
    public string $nama = '';

    #[Validate('nullable|string')]
    public string $deskripsi = '';

    #[Validate('nullable|image|max:1024')]
    public mixed $foto = null;

    #[Validate('boolean')]
    public bool $status = true;

    public function mount(): void
    {
        $this->nama = $this->slider->name;
        $this->deskripsi = $this->slider->description;
        $this->status = $this->slider->status;
        $this->foto = $this->slider->photo;
    }

    public function save(): void
    {
        $data = $this->validate();
        $this->processUpload($data);
        $this->slider->update([
            'name' => $data['nama'],
            'description' => $data['deskripsi'],
            'photo' => $data['photo'] ?? $this->slider->photo,
            'status' => $data['status'],
        ]);
        $this->success('Slider berhasil diperbarui.', redirectTo: route('sliders.index'));
    }

    private function processUpload(array &$data): void
    {
        if (!$this->foto || !($this->foto instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }
        if ($this->slider->photo) {
            $path = str($this->slider->photo)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }
        $url = $this->foto->store('sliders', 'public');
        $data['photo'] = "/storage/{$url}";
    }
}; ?>

<x-pages.layout :page-title="'Edit Slider - ' . $slider->name">
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <div
                class="mb-10 grid gap-5 lg:grid-cols-2"
                id="edit-slider-form"
            >
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Edit Data Slider"
                        size="text-xl"
                        subtitle="Perbarui informasi slider di bawah ini."
                    />
                </div>
                <div>
                    <x-mary-file
                        wire:model="foto"
                        accept="image/png, image/jpeg"
                    >
                        <img
                            class="h-36 rounded-lg"
                            src="{{ $slider->foto ?? '/images/slider-default.jpg' }}"
                        />
                    </x-mary-file>
                    <x-mary-input
                        :label="'Nama'"
                        wire:model="nama"
                    />
                    <x-mary-input
                        :label="'Deskripsi'"
                        wire:model="deskripsi"
                    />
                    <x-mary-group
                        class="[&:checked]:!btn-primary"
                        :label="'Status'"
                        wire:model="status"
                        :options="[['name' => 'Aktif', 'id' => true], ['name' => 'Nonaktif', 'id' => false]]"
                    />
                </div>
            </div>
            <x-slot:actions>
                <x-mary-button
                    class="btn-soft"
                    label="Batal"
                    :link="route('sliders.index')"
                />
                <x-mary-button
                    class="btn-primary"
                    type="submit"
                    :label="'Simpan'"
                    icon="o-paper-airplane"
                    spinner="save"
                />
            </x-slot:actions>
        </x-mary-form>
    </x-slot:content>
</x-pages.layout>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"
        rel="stylesheet"
    />
@endpush
