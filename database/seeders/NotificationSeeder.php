<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TurnitinThreadStatus;
use App\Models\TurnitinThread;
use App\Models\TurnitinThreadComment;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\SolutionMarked;
use App\Notifications\ThreadCreated;
use App\Notifications\ThreadStatusChanged;
use Illuminate\Database\Seeder;

final class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'mahasiswa')->take(2)->get();
        $lecturers = User::where('role', 'dosen')->take(2)->get();
        $threads = TurnitinThread::take(3)->get();
        $comments = TurnitinThreadComment::take(3)->get();

        if ($students->count() > 0 && $lecturers->count() > 0 && $threads->count() > 0) {
            // Create thread notification
            foreach ($lecturers as $lecturer) {
                $lecturer->notify(new ThreadCreated($threads->first(), $students->first()));
            }

            // Create new comment notifications
            if ($comments->count() > 0) {
                foreach ($students as $student) {
                    $student->notify(new NewComment($comments->first(), $threads->first(), $lecturers->first()));
                }
            }

            // Create solution marked notifications
            if ($comments->count() > 0) {
                foreach ($students as $student) {
                    $student->notify(new SolutionMarked($comments->first(), $threads->first(), $lecturers->first()));
                }
            }

            // Create status changed notifications
            foreach ($students as $student) {
                $student->notify(new ThreadStatusChanged(
                    $threads->first(),
                    TurnitinThreadStatus::OPEN,
                    TurnitinThreadStatus::PROCESSING,
                    $lecturers->first()
                ));
            }
        }
    }
}
