<?php

use Livewire\Volt\Component;
use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Enums\TurnitinThreadStatus;
use App\Enums\UserRole;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithFileUploads;

    public TurnitinThread $thread;

    #[Validate('required|string')]
    public string $comment = '';

    #[Validate('nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240')]
    public mixed $commentFile = null;

    public function mount(): void
    {
        $this->thread->load(['comments.user', 'student', 'lecturer', 'solution.user']);
    }

    public function addComment(): void
    {
        $data = $this->validate();

        $this->processCommentUpload($data);

        unset($data['commentFile']);

        $data['turnitin_thread_id'] = $this->thread->id;
        $data['user_id'] = auth()->id();

        TurnitinThreadComment::create($data);

        $this->reset(['comment', 'commentFile']);

        $this->thread->load(['comments.user']);

        $this->success('Komentar berhasil ditambahkan.');
    }
    private function processCommentUpload(array &$data): void
    {
        if (!$this->commentFile || !($this->commentFile instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        $fileName = time() . '_' . Str::random(10) . '.' . $this->commentFile->getClientOriginalExtension();

        $this->commentFile->storeAs('thread-comments', $fileName, 'public');

        $data['file'] = $fileName;
    }

    public function downloadFile()
    {
        if (!$this->thread->file_name) {
            $this->error('File tidak ditemukan.');
            return;
        }

        $path = storage_path('app/public/turnitin-threads/' . $this->thread->file_name);

        if (!file_exists($path)) {
            $this->error('File tidak ditemukan.');
            return;
        }

        return response()->download($path, $this->thread->file_original_name);
    }

    public function downloadCommentFile(TurnitinThreadComment $comment)
    {
        if (!$comment->file) {
            $this->error('File tidak ditemukan.');
            return;
        }

        $path = storage_path('app/public/thread-comments/' . $comment->file);

        if (!file_exists($path)) {
            $this->error('File tidak ditemukan.');
            return;
        }

        return response()->download($path);
    }

    public function updateStatus(int $status): void
    {
        $user = auth()->user();

        if ($user->role !== UserRole::LECTURE && $user->role !== UserRole::ADMIN) {
            $this->error('Anda tidak memiliki izin untuk mengubah status.');
            return;
        }

        $this->thread->update(['status' => $status]);
        $this->thread->refresh();

        $this->success('Status berhasil diperbarui.');
    }

    public function deleteComment(TurnitinThreadComment $comment): void
    {
        $user = auth()->user();

        // Only allow comment owner or admin to delete
        if ($user->id !== $comment->user_id && $user->role !== UserRole::ADMIN) {
            $this->error('Anda tidak memiliki izin untuk menghapus komentar ini.');
            return;
        }

        // Delete file if exists
        if ($comment->file) {
            \Storage::disk('public')->delete('thread-comments/' . $comment->file);
        }

        $comment->delete();

        $this->thread->load(['comments.user']);

        $this->success('Komentar berhasil dihapus.');
    }

    public function markAsSolution(TurnitinThreadComment $comment): void
    {
        $user = auth()->user();

        // Only allow admin and lecturer to mark solution
        if ($user->role !== UserRole::ADMIN && $user->role !== UserRole::LECTURE) {
            $this->error('Anda tidak memiliki izin untuk menandai jawaban.');
            return;
        }

        // Remove existing solution
        TurnitinThreadComment::where('turnitin_thread_id', $this->thread->id)->update(['is_solution' => false]);

        // Mark this comment as solution
        $comment->update(['is_solution' => true]);

        $this->thread->load(['comments.user', 'solution.user']);

        $this->success('Komentar berhasil ditandai sebagai jawaban.');
    }

    public function unmarkSolution(TurnitinThreadComment $comment): void
    {
        $user = auth()->user();

        // Only allow admin and lecturer to unmark solution
        if ($user->role !== UserRole::ADMIN && $user->role !== UserRole::LECTURE) {
            $this->error('Anda tidak memiliki izin untuk menghapus tanda jawaban.');
            return;
        }

        $comment->update(['is_solution' => false]);

        $this->thread->load(['comments.user', 'solution.user']);

        $this->success('Tanda jawaban berhasil dihapus.');
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'canComment' => true, // Everyone can comment
            'canEditStatus' => $user->role === UserRole::LECTURE || $user->role === UserRole::ADMIN,
            'canEdit' => $user->role === UserRole::STUDENT && $user->id === $this->thread->student_id,
            'canMarkSolution' => $user->role === UserRole::LECTURE || $user->role === UserRole::ADMIN,
        ];
    }
}; ?>

<x-pages.layout :page-title="'Thread - ' . $thread->name">
    <x-slot:actions>
        @if ($canEdit)
            <x-mary-button
                class="btn-soft btn"
                label="Edit"
                :link="route('turnitin-threads.edit', ['thread' => $thread->id])"
                icon="o-pencil"
                responsive
            />
        @endif
        <x-mary-button
            class="btn-soft btn"
            label="Kembali"
            :link="route('turnitin-threads.index')"
            icon="o-arrow-left"
            responsive
        />
    </x-slot:actions>

    <x-slot:content>
        <!-- Thread Information -->
        <div class="mb-8">
            <x-mary-card>
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold">{{ $thread->name }}</h2>
                        <p class="text-sm text-gray-600">
                            {{ $thread->datetime->format('d F Y, H:i') }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <x-mary-badge
                            @class([
                                'badge-soft' => $thread->status === TurnitinThreadStatus::OPEN,
                                'badge-warning' => $thread->status === TurnitinThreadStatus::PROCESSING,
                                'badge-success' => $thread->status === TurnitinThreadStatus::DONE,
                            ])
                            :value="$thread->status->label()"
                        />
                        @if ($canEditStatus)
                            <x-mary-dropdown>
                                <x-slot:trigger>
                                    <x-mary-button
                                        class="btn-sm btn-circle btn-outline"
                                        icon="o-ellipsis-horizontal"
                                    />
                                </x-slot:trigger>
                                <x-mary-menu-item
                                    title="Ubah ke Dibuat"
                                    wire:click="updateStatus({{ TurnitinThreadStatus::OPEN->value }})"
                                />
                                <x-mary-menu-item
                                    title="Ubah ke Diproses"
                                    wire:click="updateStatus({{ TurnitinThreadStatus::PROCESSING->value }})"
                                />
                                <x-mary-menu-item
                                    title="Ubah ke Selesai"
                                    wire:click="updateStatus({{ TurnitinThreadStatus::DONE->value }})"
                                />
                            </x-mary-dropdown>
                        @endif
                    </div>
                </div>

                <!-- Participants -->
                <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Mahasiswa</label>
                        <div class="mt-1 flex items-center gap-2">
                            <x-mary-avatar
                                class="!w-8 !rounded-lg"
                                image="{{ $thread->student->avatar ?? '/images/empty-user.jpg' }}"
                            />
                            <span>{{ $thread->student->name }}</span>
                            <span class="text-sm text-gray-500">({{ $thread->student->email }})</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Dosen</label>
                        <div class="mt-1 flex items-center gap-2">
                            <x-mary-avatar
                                class="!w-8 !rounded-lg"
                                image="{{ $thread->lecturer->avatar ?? '/images/empty-user.jpg' }}"
                            />
                            <span>{{ $thread->lecturer->name }}</span>
                            <span class="text-sm text-gray-500">({{ $thread->lecturer->email }})</span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if ($thread->description)
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700">Deskripsi</label>
                        <p class="mt-1 text-gray-900">{{ $thread->description }}</p>
                    </div>
                @endif

                <!-- File -->
                @if ($thread->file_original_name)
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-700">File Dokumen</label>
                        <div class="mt-1 flex items-center gap-2 rounded-lg bg-gray-50 p-3">
                            <x-mary-icon
                                class="h-5 w-5 text-blue-600"
                                name="o-document"
                            />
                            <span class="flex-1 text-sm">{{ $thread->file_original_name }}</span>
                            <a
                                class="btn btn-sm btn-outline btn-primary"
                                href="{{ '/storage/turnitin-threads/' . $thread->file_name }}"
                                target="_blank"
                            >
                                <x-mary-icon
                                    class="h-4 w-4"
                                    name="o-arrow-down-tray"
                                />
                                Download
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Solution -->
                @if ($thread->solution)
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                            <x-mary-icon
                                class="h-4 w-4 text-yellow-500"
                                name="o-star"
                            />
                            Jawaban Terpilih
                        </label>
                        <div class="mt-1 rounded-lg border-2 border-yellow-200 bg-yellow-50 p-4">
                            <div class="flex items-start gap-3">
                                <x-mary-avatar
                                    class="!w-8 !rounded-lg"
                                    image="{{ $thread->solution->user->avatar ?? '/images/empty-user.jpg' }}"
                                />
                                <div class="flex-1">
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-medium text-yellow-800">{{ $thread->solution->user->name }}</span>
                                            <span class="text-xs text-yellow-600">
                                                {{ $thread->solution->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        @if ($canMarkSolution)
                                            <x-mary-button
                                                class="btn-xs btn-circle btn-ghost text-yellow-600 hover:bg-yellow-600 hover:text-white"
                                                icon="o-x-mark"
                                                wire:click="unmarkSolution({{ $thread->solution->id }})"
                                                wire:confirm="Hapus tanda jawaban ini?"
                                                spinner="unmarkSolution"
                                            />
                                        @endif
                                    </div>
                                    <p class="mb-2 text-yellow-800">{{ $thread->solution->comment }}</p>
                                    @if ($thread->solution->file)
                                        <div class="flex items-center gap-2 rounded bg-yellow-100 p-2 text-sm">
                                            <x-mary-icon
                                                class="h-4 w-4 text-yellow-600"
                                                name="o-paper-clip"
                                            />
                                            <span class="flex-1 text-yellow-700">Lampiran file</span>
                                            <a
                                                class="font-medium text-yellow-700 hover:text-yellow-900"
                                                href="{{ '/storage/thread-comments/' . $thread->solution->file }}"
                                                target="_blank"
                                            >
                                                Download
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </x-mary-card>
        </div>

        <!-- Comments Section -->
        <div class="mb-8">
            <x-mary-card>
                <x-mary-header
                    class="!mb-6"
                    title="Komentar ({{ $thread->comments->count() }})"
                    size="text-lg"
                />

                <!-- Comments List -->
                <div class="mb-6 space-y-4">
                    @forelse($thread->comments as $comment)
                        <div @class([
                            'border-l-4 py-2 pl-4',
                            'border-yellow-400 bg-yellow-50' => $comment->is_solution,
                            'border-blue-200' => !$comment->is_solution,
                        ])>
                            <div class="flex items-start gap-3">
                                <x-mary-avatar
                                    class="!w-8 !rounded-lg"
                                    image="{{ $comment->user->avatar ?? '/images/empty-user.jpg' }}"
                                />
                                <div class="flex-1">
                                    <div class="mb-1 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center gap-1">
                                                @if ($comment->is_solution)
                                                    <x-mary-icon
                                                        class="h-3 w-3 text-yellow-500"
                                                        name="o-star"
                                                    />
                                                @endif
                                                <span
                                                    class="{{ $comment->is_solution ? 'text-yellow-800' : '' }} font-medium"
                                                >{{ $comment->user->name }}</span>
                                            </div>
                                            <span
                                                class="{{ $comment->is_solution ? 'text-yellow-600' : 'text-gray-500' }} text-xs"
                                            >
                                                {{ $comment->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            @if ($canMarkSolution && !$comment->is_solution)
                                                <x-mary-button
                                                    class="btn-xs btn-circle btn-ghost text-yellow-500 hover:bg-yellow-500 hover:text-white"
                                                    icon="o-star"
                                                    wire:click="markAsSolution({{ $comment->id }})"
                                                    wire:confirm="Tandai sebagai jawaban terpilih?"
                                                    spinner="markAsSolution"
                                                />
                                            @endif
                                            @if (auth()->id() === $comment->user_id || auth()->user()->role === UserRole::ADMIN)
                                                <x-mary-button
                                                    class="btn-xs btn-circle btn-ghost text-error hover:bg-error hover:text-white"
                                                    icon="o-trash"
                                                    wire:click="deleteComment({{ $comment->id }})"
                                                    spinner="deleteComment"
                                                    wire:confirm="Apakah Anda yakin ingin menghapus komentar ini?"
                                                />
                                            @endif
                                        </div>
                                    </div>
                                    <p class="{{ $comment->is_solution ? 'text-yellow-800' : 'text-gray-900' }} mb-2">
                                        {{ $comment->comment }}</p>
                                    @if ($comment->file)
                                        <div class="flex items-center gap-2 rounded bg-gray-50 p-2 text-sm">
                                            <x-mary-icon
                                                class="h-4 w-4 text-gray-600"
                                                name="o-paper-clip"
                                            />
                                            <span class="flex-1">Lampiran file</span>
                                            <a
                                                class="text-blue-600 hover:text-blue-800"
                                                href="{{ '/storage/thread-comments/' . $comment->file }}"
                                                target="_blank"
                                            >
                                                Download
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-500">
                            <x-mary-icon
                                class="mx-auto mb-2 h-12 w-12 text-gray-300"
                                name="o-chat-bubble-left-right"
                            />
                            <p>Belum ada komentar pada thread ini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                @if ($canComment)
                    <div class="border-t pt-6">
                        <x-mary-form wire:submit="addComment">
                            <x-mary-textarea
                                label="Tambah Komentar"
                                wire:model="comment"
                                placeholder="Tulis komentar Anda..."
                                rows="3"
                            />

                            <x-mary-file
                                label="Lampiran (Opsional)"
                                wire:model="commentFile"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                hint="Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 10MB"
                            />

                            <div class="flex justify-end">
                                <x-mary-button
                                    class="btn-primary"
                                    type="submit"
                                    label="Kirim Komentar"
                                    icon="o-paper-airplane"
                                    spinner="addComment"
                                />
                            </div>
                        </x-mary-form>
                    </div>
                @endif
            </x-mary-card>
        </div>
    </x-slot:content>
</x-pages.layout>
