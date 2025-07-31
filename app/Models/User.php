<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'avatar',
        'name',
        'email',
        'status',
        'role',
        'password',
        'email_verified_at',

        // Notification preferences
        'push_notifications_enabled',
        'email_notifications_enabled',
        'thread_created_notifications',
        'new_comment_notifications',
        'solution_marked_notifications',
        'thread_status_notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->pipe(
                fn ($parts) => Str::upper(
                    Str::substr($parts->first(), 0, 1) .
                        Str::substr($parts->last(), 0, 1)
                )
            );
    }

    /**
     * Get the turnitin threads as student
     */
    public function studentThreads()
    {
        return $this->hasMany(TurnitinThread::class, 'student_id');
    }

    /**
     * Get the turnitin threads as lecturer
     */
    public function lectureThreads()
    {
        return $this->hasMany(TurnitinThread::class, 'lecture_id');
    }

    /**
     * Get the turnitin thread comments
     */
    public function turnitinComments()
    {
        return $this->hasMany(TurnitinThreadComment::class);
    }

    /**
     * Get user setting value
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        // For now, we'll use session-based settings
        // In production, you might want to create a user_settings table
        return session("user_settings.{$this->id}.{$key}", $default);
    }

    /**
     * Set user setting value
     */
    public function setSetting(string $key, mixed $value): void
    {
        session(["user_settings.{$this->id}.{$key}" => $value]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'role' => UserRole::class,
        ];
    }
}
