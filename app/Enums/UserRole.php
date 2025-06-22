<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case LECTURE = 'dosen';
    case STUDENT = 'mahasiswa';

    public static function fromLabel(string $label): ?self
    {
        return match (mb_strtolower($label)) {
            'admin' => self::ADMIN,
            'dosen' => self::LECTURE,
            'mahasiswa' => self::STUDENT,
            default => null,
        };
    }

    public static function all(string $key = 'id', string $value = 'name'): array
    {
        return [
            [$key => self::ADMIN, $value => self::ADMIN->label()],
            [$key => self::LECTURE, $value => self::LECTURE->label()],
            [$key => self::STUDENT, $value => self::STUDENT->label()],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::LECTURE => 'Dosen',
            self::STUDENT => 'Mahasiswa',
        };
    }
}
