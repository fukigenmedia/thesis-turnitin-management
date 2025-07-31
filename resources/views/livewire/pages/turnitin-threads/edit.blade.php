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

new class extends Component {
    use Toast, WithFileUploads;

    public TurnitinThread $thread;

    #[Validate('required|date')]
    public string $datetime = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string')]
    public string $description = '';

    #[Validate('nullable|file|mimes:pdf,doc,docx|max:10240')]
    public mixed $file = null;

    // Admin only fields - no validation attributes for students
    public ?int $student_id = null;
    public ?int $lecture_id = null;
    public int $status;

    public array $studentOptions = [];
    public array $lecturerOptions = [];
    public array $statusOptions = [];

    public function mount(): void
    {
        // Additional authorization check
        $user = auth()->user();
        if ($user->role !== UserRole::STUDENT || $user->id !== $this->thread->student_id) {
            abort(403, 'Unauthorized. Only the thread creator can edit this thread.');
        }

        $this->fill($this->thread);
        $this->datetime = $this->thread->datetime->format('Y-m-d\TH:i');

        $this->loadOptions();
    }
    private function loadOptions(): void
    {
        $this->studentOptions = User::where('role', UserRole::STUDENT)->orderBy('name')->get()->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->toArray();

        $this->lecturerOptions = User::where('role', UserRole::LECTURE)->orderBy('name')->get()->map(fn($user) => ['id' => $user->id, 'name' => $user->name])->toArray();

        $this->statusOptions = TurnitinThreadStatus::all();
    }

    public function save(): void
    {
        $user = auth()->user();

        // Custom validation based on user role
        if ($user->role === UserRole::ADMIN) {
            $data = $this->validate([
                'datetime' => 'required|date',
                'student_id' => 'required|exists:users,id',
                'lecture_id' => 'required|exists:users,id',
                'status' => 'required|int',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            ]);
        } else {
            $data = $this->validate([
                'datetime' => 'required|date',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            ]);
        }

        $this->processUpload($data);

        // Remove file from data since it's not a database field
        unset($data['file']);

        // Students can only edit specific fields
        if ($user->role === UserRole::STUDENT) {
            // Students can only edit: datetime, name, description, and file
            $allowedFields = ['datetime', 'name', 'description', 'file_original_name', 'file_name'];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }

        $this->thread->update($data);

        $this->success('Thread berhasil diperbarui.', redirectTo: route('turnitin-threads.index'));
    }
    private function processUpload(array &$data): void
    {
        if (!$this->file || !($this->file instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        // Delete old file if exists
        if ($this->thread->file_name) {
            \Storage::disk('public')->delete('turnitin-threads/' . $this->thread->file_name);
        }

        $originalName = $this->file->getClientOriginalName();
        $fileName = time() . '_' . Str::random(10) . '.' . $this->file->getClientOriginalExtension();

        $this->file->storeAs('turnitin-threads', $fileName, 'public');

        $data['file_original_name'] = $originalName;
        $data['file_name'] = $fileName;
    }

    public function removeFile(): void
    {
        if ($this->thread->file_name) {
            \Storage::disk('public')->delete('turnitin-threads/' . $this->thread->file_name);
        }

        $this->thread->update([
            'file_original_name' => null,
            'file_name' => null,
        ]);

        $this->success('File berhasil dihapus.');
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'isStudent' => $user->role === UserRole::STUDENT,
            'isLecturer' => $user->role === UserRole::LECTURE,
            'isAdmin' => $user->role === UserRole::ADMIN,
            'canEditStatus' => $user->role === UserRole::LECTURE || $user->role === UserRole::ADMIN,
        ];
    }
}; ?>

<x-pages.layout :page-title="'Edit Thread - ' . $thread->name">
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <div class="mb-10 grid gap-5 lg:grid-cols-2">
                <div>
                    <x-mary-header
                        class="!mb-6"
                        title="Edit Thread Turnitin"
                        size="text-xl"
                        subtitle="Perbarui informasi thread turnitin."
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

                    @if ($isAdmin)
                        <x-mary-select
                            label="Mahasiswa"
                            wire:model="student_id"
                            :options="$studentOptions"
                            option-value="id"
                            option-label="name"
                            placeholder="Pilih mahasiswa"
                        />
                    @else
                        <x-mary-input
                            value="{{ $thread->student->name }}"
                            label="Mahasiswa"
                            readonly
                        />
                    @endif

                    @if ($isAdmin)
                        <x-mary-select
                            label="Dosen"
                            wire:model="lecture_id"
                            :options="$lecturerOptions"
                            option-value="id"
                            option-label="name"
                            placeholder="Pilih dosen"
                        />
                    @else
                        <x-mary-input
                            value="{{ $thread->lecturer->name }}"
                            label="Dosen"
                            readonly
                        />
                    @endif

                    @if ($isAdmin)
                        <x-mary-group
                            class="[&:checked]:!btn-primary"
                            label="Status"
                            wire:model="status"
                            :options="$statusOptions"
                        />
                    @else
                        <x-mary-input
                            value="{{ $thread->status->label() }}"
                            label="Status"
                            readonly
                        />
                    @endif

                    @if ($thread->file_original_name)
                        <div class="mb-4">
                            <label class="label">
                                <span class="label-text">File Saat Ini</span>
                            </label>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                <div class="flex items-center gap-2">
                                    <x-mary-icon
                                        class="h-5 w-5 text-blue-600"
                                        name="o-document"
                                    />
                                    <span class="text-sm">{{ $thread->file_original_name }}</span>
                                </div>
                                <x-mary-button
                                    class="btn-sm btn-outline btn-error"
                                    label="Hapus"
                                    icon="o-trash"
                                    wire:click="removeFile"
                                    spinner="removeFile"
                                />
                            </div>
                        </div>
                    @endif

                    <x-mary-file
                        label="{{ $thread->file_original_name ? 'Ganti File Dokumen' : 'File Dokumen' }}"
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
