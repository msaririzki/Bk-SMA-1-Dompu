<?php

namespace App\Enums;

enum DocumentType: string
{
    case ViolationRecap = 'violation_recap';
    case Statement = 'statement';
    case ParentSummons = 'parent_summons';
    case MeetingReport = 'meeting_report';
    case Suspension = 'suspension';
    case HomeVisit = 'home_visit';

    public function label(): string
    {
        return match ($this) {
            self::ViolationRecap => 'Rekap Pelanggaran', self::Statement => 'Surat Pernyataan',
            self::ParentSummons => 'Surat Panggilan Orang Tua', self::MeetingReport => 'Berita Acara Pertemuan',
            self::Suspension => 'Surat Skorsing', self::HomeVisit => 'Laporan Home Visit',
        };
    }
}
