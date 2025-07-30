<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TurnitinThreadStatus;
use App\Enums\UserRole;
use App\Models\TurnitinThread;
use App\Models\User;
use Illuminate\Database\Seeder;

final class TurnitinThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', UserRole::STUDENT)->get();
        $lecturers = User::where('role', UserRole::LECTURE)->get();

        if ($students->isEmpty() || $lecturers->isEmpty()) {
            $this->command->warn('No students or lecturers found. Please seed users first.');

            return;
        }

        $threads = [
            [
                'name' => 'Analisis Sentimen Media Sosial',
                'description' => 'Penelitian tentang analisis sentimen terhadap postingan media sosial menggunakan machine learning.',
                'status' => TurnitinThreadStatus::OPEN,
            ],
            [
                'name' => 'Sistem Informasi Akademik Berbasis Web',
                'description' => 'Pengembangan sistem informasi akademik menggunakan Laravel dan MySQL.',
                'status' => TurnitinThreadStatus::PROCESSING,
            ],
            [
                'name' => 'Implementasi Blockchain untuk Supply Chain',
                'description' => 'Studi implementasi teknologi blockchain dalam manajemen supply chain.',
                'status' => TurnitinThreadStatus::DONE,
            ],
            [
                'name' => 'Mobile App untuk E-Learning',
                'description' => 'Pengembangan aplikasi mobile untuk platform pembelajaran online.',
                'status' => TurnitinThreadStatus::OPEN,
            ],
            [
                'name' => 'AI Chatbot untuk Customer Service',
                'description' => 'Implementasi chatbot berbasis AI untuk meningkatkan layanan customer service.',
                'status' => TurnitinThreadStatus::PROCESSING,
            ],
        ];

        foreach ($threads as $index => $threadData) {
            $student = $students->random();
            $lecturer = $lecturers->random();

            TurnitinThread::create([
                'datetime' => now()->subDays(rand(1, 30))->addHours(rand(8, 17)),
                'student_id' => $student->id,
                'lecture_id' => $lecturer->id,
                'status' => $threadData['status'],
                'name' => $threadData['name'],
                'description' => $threadData['description'],
                'file_original_name' => null,
                'file_name' => null,
            ]);
        }

        $this->command->info('TurnitinThread seeder completed successfully.');
    }
}
