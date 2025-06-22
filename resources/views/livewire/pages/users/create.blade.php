<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Enums\UserStatus;
use App\Enums\UserRole;
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithFileUploads;

    #[Validate('required|max:100')]
    public string $name = '';

    #[Validate('required|email|max:50|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8|max:255|confirmed')]
    public string $password = '';
    public string $password_confirmation = '';

    #[Validate('nullable|image|max:1024')]
    public mixed $avatar = null;

    #[Validate('required|int')]
    public int $status;

    #[Validate('required|string|in:admin,dosen,mahasiswa')]
    public string $role = 'mahasiswa';

    public array $statusOptions;

    public array $roleOptions;

    public function mount(): void
    {
        $this->status = UserStatus::ACTIVE->value;

        $this->statusOptions = UserStatus::all();
        $this->roleOptions = UserRole::all();
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->processUpload($data);

        $data['email_verified_at'] = now();
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);

        $this->success('Pengguna berhasil dibuat.', redirectTo: route('users.index'));
    }

    private function processUpload(array &$data): void
    {
        if (!$this->avatar || !($this->avatar instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        $url = $this->avatar->store('users', 'public');
        $data['avatar'] = "/storage/{$url}";
    }
}; ?>

<x-pages.layout
    page-title="Tambah Pengguna"
    page-subtitle="Menambahkan pengguna baru ke sistem."
>
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <div
                class="mb-10 grid gap-5 lg:grid-cols-2"
                id="create-user-form"
            >
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Data Pengguna Baru"
                        size="text-xl"
                        subtitle="Isi informasi pengguna baru di bawah ini."
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
                            src="/images/empty-user.jpg"
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

            <div
                class="mb-10 grid gap-5 lg:grid-cols-2"
                id="create-user-password-form"
            >
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Password Pengguna Baru"
                        size="text-xl"
                        subtitle="Masukkan password untuk pengguna baru."
                    />
                </div>

                <div>
                    <x-mary-input
                        type="password"
                        :label="__('Password')"
                        wire:model="password"
                        placeholder="Masukkan password baru"
                    />

                    <x-mary-input
                        type="password"
                        :label="__('Konfirmasi Password')"
                        wire:model="password_confirmation"
                        placeholder="Konfirmasi password baru"
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
