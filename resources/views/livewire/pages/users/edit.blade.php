<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Enums\UserStatus;
use App\Enums\UserRole;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    #[Validate('required|max:100')]
    public string $name = '';

    #[Validate('required|email|max:50')]
    public string $email = '';

    #[Validate('nullable|image|max:1024')]
    public mixed $avatar = null;

    #[Validate('required|int')]
    public int $status;

    #[Validate('required|string|in:admin,dosen,mahasiswa')]
    public string $role = 'mahasiswa';

    public array $statusOptions = [];
    public array $roleOptions = [];

    public function mount(): void
    {
        $this->fill($this->user);

        $this->statusOptions = UserStatus::all();
        $this->roleOptions = UserRole::all();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'email' => ['required', 'email', 'max:50', Rule::unique(User::class)->ignore($this->user->id)],
            'avatar' => 'nullable|image|max:1024',
            'status' => 'required|int',
            'role' => 'required|string|in:admin,dosen,mahasiswa',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->processUpload($data);

        $this->user->update($data);

        $this->success('Pengguna berhasil diperbarui.', redirectTo: route('users.index'));
    }

    private function processUpload(array &$data): void
    {
        if (!$this->avatar || !($this->avatar instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        if ($this->user->avatar) {
            $path = str($this->user->avatar)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }

        $url = $this->avatar->store('users', 'public');
        $data['avatar'] = "/storage/{$url}";
    }
}; ?>

<x-pages.layout :page-title="'Edit Pengguna - ' . $user->name">
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <div
                class="mb-10 grid gap-5 lg:grid-cols-2"
                id="edit-user-form"
            >
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Edit Data Pengguna"
                        size="text-xl"
                        subtitle="Perbarui informasi pengguna di bawah ini."
                    />
                </div>
                <div>
                    <x-mary-file
                        wire:model="avatar"
                        accept="image/png, image/jpeg"
                        crop-after-change
                    >
                        <img
                            class="h-36 rounded-lg"
                            src="{{ $user->avatar ?? '/images/empty-user.jpg' }}"
                        />
                    </x-mary-file>

                    <x-mary-input
                        :label="__('Name')"
                        wire:model="name"
                    />
                    <x-mary-input
                        :label="__('Email')"
                        wire:model="email"
                    />
                    <x-mary-group
                        class="[&:checked]:!btn-primary"
                        :label="__('Status')"
                        wire:model="status"
                        :options="$statusOptions"
                    />
                    <x-mary-group
                        class="[&:checked]:!btn-primary"
                        label="Tipe Akun"
                        wire:model="role"
                        :options="$roleOptions"
                    />
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button
                    class="btn-soft"
                    label="Batal"
                    :link="route('users.index')"
                />
                <x-mary-button
                    class="btn-primary"
                    type="submit"
                    :label="__('Save')"
                    icon="o-paper-airplane"
                    spinner="save"
                />
            </x-slot:actions>
        </x-mary-form>
    </x-slot:content>
</x-pages.layout>

@push('scripts')
    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"
        rel="stylesheet"
    />
@endpush
