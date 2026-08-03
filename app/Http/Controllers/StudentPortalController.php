<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Services\ScoringService;

class StudentPortalController extends Controller
{
    public function __invoke(ScoringService $scoring)
    {
        $student = request()->user()->student;
        abort_unless($student, 403);
        $year = AcademicYear::active();
        $student->load(['currentEnrollment.schoolClass', 'cases' => fn ($q) => $q->with('items')->where('academic_year_id', $year?->id)->where('status', '!=', 'cancelled')->latest('occurred_at')]);

        return view('student.portal', ['student' => $student, 'year' => $year, 'score' => $year ? $scoring->summary($student, $year->id) : null]);
    }
}
