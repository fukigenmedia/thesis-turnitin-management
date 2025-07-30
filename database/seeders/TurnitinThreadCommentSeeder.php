<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TurnitinThreadCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $threads = TurnitinThread::with(['student', 'lecturer'])->get();
        $users = User::all();

        if ($threads->isEmpty()) {
            $this->command->warn('No turnitin threads found. Please seed threads first.');

            return;
        }

        $comments = [
            'Terima kasih atas submission yang sudah dikirimkan. Saya akan review terlebih dahulu.',
            'Mohon diperbaiki bagian metodologi penelitiannya.',
            'Draft sudah bagus, namun perlu penambahan referensi di bagian tinjauan pustaka.',
            'Sudah saya perbaiki sesuai saran yang diberikan.',
            'Apakah ada format khusus untuk penulisan daftar pustaka?',
            'Silakan gunakan format APA Style untuk referensi.',
            'Bagian hasil dan pembahasan sudah cukup komprehensif.',
            'Perlu ditambahkan kesimpulan dan saran untuk penelitian selanjutnya.',
            'File sudah saya update dengan revisi terbaru.',
            'Siap untuk tahap selanjutnya setelah perbaikan minor.',
        ];

        foreach ($threads as $thread) {
            // Add random comments from student and lecturer
            $numComments = rand(2, 5);

            for ($i = 0; $i < $numComments; $i++) {
                // Alternate between student and lecturer comments
                $isStudentComment = $i % 2 === 0;
                $commentUser = $isStudentComment ? $thread->student : $thread->lecturer;

                TurnitinThreadComment::create([
                    'turnitin_thread_id' => $thread->id,
                    'user_id' => $commentUser->id,
                    'comment' => $comments[array_rand($comments)],
                    'file' => null,
                    'created_at' => $thread->created_at->addDays(rand(1, 5))->addHours(rand(1, 12)),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('TurnitinThreadComment seeder completed successfully.');
    }
}
