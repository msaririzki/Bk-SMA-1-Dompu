<?php

namespace App\Services;

use App\Models\SeverityLevel;
use App\Models\Student;

class ScoringService
{
    public function summary(Student $student, int $academicYearId): array
    {
        $points = $student->pointsForYear($academicYearId);
        $threshold = max(
            (int) SeverityLevel::max('max_points'),
            (int) SeverityLevel::max('min_points'),
        );
        $threshold = max(100, $threshold);

        return [
            'annual_points' => $points,
            'all_time_points' => $student->allTimePoints(),
            'percentage' => min(100, round(($points / $threshold) * 100)),
            'severity' => SeverityLevel::forPoints($points),
            'threshold' => $threshold,
        ];
    }
}
