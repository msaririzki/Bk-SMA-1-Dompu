<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Graduated = 'graduated';
    case Transferred = 'transferred';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif', self::Graduated => 'Lulus',
            self::Transferred => 'Pindah', self::Withdrawn => 'Keluar',
        };
    }
}
