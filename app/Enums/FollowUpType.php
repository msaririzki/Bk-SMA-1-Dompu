<?php

namespace App\Enums;

enum FollowUpType: string
{
    case Coaching = 'coaching';
    case VerbalWarning = 'verbal_warning';
    case WrittenWarning = 'written_warning';
    case ParentSummons = 'parent_summons';
    case HomeVisit = 'home_visit';
    case Suspension = 'suspension';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Coaching => 'Pembinaan', self::VerbalWarning => 'Teguran Lisan',
            self::WrittenWarning => 'Peringatan Tertulis', self::ParentSummons => 'Pemanggilan Orang Tua',
            self::HomeVisit => 'Home Visit', self::Suspension => 'Skorsing', self::Other => 'Lainnya',
        };
    }
}
