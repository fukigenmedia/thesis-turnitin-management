<?php

declare(strict_types=1);

namespace App\Enums;

enum TurnitinThreadStatus: int
{
    case OPEN = 0;
    case PROCESSING = 1;
    case DONE = 2;

    public static function fromLabel(string $label): ?self
    {
        return match (mb_strtolower($label)) {
            'dibuat' => self::OPEN,
            'diproses' => self::PROCESSING,
            'selesai' => self::DONE,
            default => null,
        };
    }

    public static function all(string $key = 'id', string $value = 'name'): array
    {
        return [
            [$key => self::OPEN, $value => self::OPEN->label()],
            [$key => self::PROCESSING, $value => self::PROCESSING->label()],
            [$key => self::DONE, $value => self::DONE->label()],
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Dibuat',
            self::PROCESSING => 'Diproses',
            self::DONE => 'Selesai',
        };
    }
}
