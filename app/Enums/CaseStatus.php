<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Open = 'open';
    case InFollowUp = 'in_follow_up';
    case Resolved = 'resolved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka', self::InFollowUp => 'Ditindaklanjuti',
            self::Resolved => 'Selesai', self::Cancelled => 'Dibatalkan',
        };
    }
}
