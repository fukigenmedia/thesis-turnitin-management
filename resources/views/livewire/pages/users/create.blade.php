<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Enums\UserStatus;
use App\Notifications\UserCreated;
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithFileUploads;

    #[Validate('required|max:100')]
    public string $name = '';

    #[Validate('required|email|max:50|unique:users')]
    public string $email = '';

    public string $password = '';

    #[Validate('nullable|image|max:1024')]
    public mixed $avatar = null;

    #[Validate('required|int')]
    public int $status;

    public array $statusOptions;

    public function mount(): void
    {
        $this->status = UserStatus::ACTIVE->value;
        $this->statusOptions = UserStatus::all();
    }

    public function save(): void
    {
        $data = $this->validate();

        $randomPassword = Str::password(12);
        $data['password'] = Hash::make(value: $randomPassword);

        $this->processUpload($data);

        $user = User::create($data);

        $user->notify(new UserCreated($randomPassword));

        $this->success(__("User {$user->name} created with success."), redirectTo: route('users.index'));
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

<x-pages.layout :page-title="__('Create User')">
    <x-slot:content>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-mary-form wire:submit="save">
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

                <x-slot:actions>
                    <x-mary-button
                        class="btn-soft"
                        :label="__('Cancel')"
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
            <div class="hidden place-self-center lg:block">
                <img
                    class="mx-auto"
                    src="/images/user-action-page.svg"
                    width="300"
                />
            </div>
        </div>
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
