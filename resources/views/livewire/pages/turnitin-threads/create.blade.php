<?php

use Livewire\Volt\Component;
use App\Models\TurnitinThread;
use App\Models\User;
use App\Enums\TurnitinThreadStatus;
use App\Enums\UserRole;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use App\Services\NotificationService;

new class extends Component {
    use Toast, WithFileUploads;

    #[Validate('required|date')]
    public string $datetime = '';

    #[Validate('required|exists:users,id')]
    public ?int $student_id = null;

    #[Validate('required|exists:users,id')]
    public ?int $lecture_id = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|file|mimes:pdf,doc,docx|max:10240')]
    public mixed $file = null;

    public array $studentOptions = [];
    public array $lecturerOptions = [];

    public function mount(): void
    {
        $this->datetime = now()->format('Y-m-d\TH:i');

        $user = auth()->user();

        // Set default values based on role
        if ($user->role === UserRole::STUDENT) {
            $this->student_id = $user->id;
        } elseif ($user->role === UserRole::LECTURE) {
            $this->lecture_id = $user->id;
        }

        $this->loadUsers();
    }

    private function loadUsers(): void
    {
        $this->studentOptions = User::where('role', UserRole::STUDENT)->orderBy('name')->get()->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->toArray();

        $this->lecturerOptions = User::where('role', UserRole::LECTURE)->orderBy('name')->get()->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->toArray();
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->processUpload($data);

        unset($data['file']);

        $data['status'] = TurnitinThreadStatus::OPEN;

        $thread = TurnitinThread::create($data);

        // Send notification
        $notificationService = app(NotificationService::class);
        $notificationService->sendThreadCreatedNotification($thread, auth()->user());

        $this->success('Thread berhasil dibuat.', redirectTo: route('turnitin-threads.index'));
    }
    private function processUpload(array &$data): void
    {
        if (!$this->file || !($this->file instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        $originalName = $this->file->getClientOriginalName();
        $fileName = time() . '_' . Str::random(10) . '.' . $this->file->getClientOriginalExtension();

        $this->file->storeAs('turnitin-threads', $fileName, 'public');

        $data['file_original_name'] = $originalName;
        $data['file_name'] = $fileName;
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'isStudent' => $user->role === UserRole::STUDENT,
            'isLecturer' => $user->role === UserRole::LECTURE,
        ];
    }
}; ?>

<x-pages.layout
    page-title="Tambah Thread Turnitin"
    page-subtitle="Membuat thread turnitin baru."
>
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <div class="mb-10 grid gap-5 lg:grid-cols-2">
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Informasi Thread Baru"
                        size="text-xl"
                        subtitle="Isi informasi thread turnitin di bawah ini."
                    />
                </div>

                <div>
                    <x-mary-input
                        type="datetime-local"
                        label="Tanggal & Waktu"
                        wire:model="datetime"
                    />

                    <x-mary-input
                        label="Nama Thread"
                        wire:model="name"
                        placeholder="Masukkan nama thread"
                    />

                    <x-mary-textarea
                        label="Deskripsi"
                        wire:model="description"
                        placeholder="Masukkan deskripsi thread (opsional)"
                        rows="3"
                    />

                    @if (!$isStudent)
                        <x-mary-select
                            label="Mahasiswa"
                            wire:model="student_id"
                            :options="$studentOptions"
                            option-value="id"
                            option-label="name"
                            placeholder="Pilih mahasiswa"
                        />
                    @endif

                    @if (!$isLecturer)
                        <x-mary-select
                            label="Dosen"
                            wire:model="lecture_id"
                            :options="$lecturerOptions"
                            option-value="id"
                            option-label="name"
                            placeholder="Pilih dosen"
                        />
                    @endif

                    <x-mary-file
                        label="File Dokumen"
                        wire:model="file"
                        accept=".pdf,.doc,.docx"
                        hint="Format: PDF, DOC, DOCX. Maksimal 10MB"
                    />
                </div>
            </div>

            <x-slot:actions>
                <x-mary-button
                    class="btn-soft"
                    label="Batal"
                    :link="route('turnitin-threads.index')"
                />
                <x-mary-button
                    class="btn-primary"
                    type="submit"
                    label="Simpan"
                    icon="o-paper-airplane"
                    spinner="save"
                />
            </x-slot:actions>
        </x-mary-form>
    </x-slot:content>
</x-pages.layout>
