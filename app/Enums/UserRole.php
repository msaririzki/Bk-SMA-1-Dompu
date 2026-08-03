<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Coordinator = 'coordinator_bk';
    case Counselor = 'guru_bk';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Coordinator => 'Koordinator BK',
            self::Counselor => 'Guru BK',
            self::Student => 'Siswa',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Student;
    }
}
