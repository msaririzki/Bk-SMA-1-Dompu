<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\FollowUp;
use App\Models\SeverityLevel;
use App\Models\Student;
use App\Models\ViolationCase;
use App\Models\ViolationInstrument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $year = AcademicYear::active();
        $priority = [];
        $severityCounts = [];
        $classDistribution = [];
        if ($year) {
            $priority = Student::query()->select('students.*')->selectSub(function ($q) use ($year) {
                $q->from('case_items')->join('violation_cases', 'violation_cases.id', '=', 'case_items.case_id')->selectRaw('COALESCE(SUM(case_items.points),0)')->whereColumn('violation_cases.student_id', 'students.id')->where('violation_cases.academic_year_id', $year->id)->where('violation_cases.status', '!=', 'cancelled');
            }, 'annual_points')->get()->filter(fn ($student) => (int) $student->annual_points > 0)->sortByDesc('annual_points')->take(8);
            $totals = DB::table('violation_cases')->join('case_items', 'case_items.case_id', '=', 'violation_cases.id')->where('violation_cases.academic_year_id', $year->id)->where('violation_cases.status', '!=', 'cancelled')->groupBy('violation_cases.student_id')->pluck(DB::raw('SUM(case_items.points)'), 'violation_cases.student_id');
            $severityCounts = SeverityLevel::orderBy('min_points')->get()->map(fn ($level) => ['name' => $level->name, 'color' => $level->color, 'total' => $totals->filter(fn ($points) => $level->contains((int) $points))->count()]);
            $classDistribution = Enrollment::query()->select('classes.name')->selectRaw('COUNT(enrollments.id) total')->join('classes', 'classes.id', '=', 'enrollments.class_id')->where('enrollments.academic_year_id', $year->id)->groupBy('classes.id', 'classes.name')->orderBy('classes.name')->get();
        }
        $backupStatus = null;
        $statusPath = storage_path('app/private/backup-status.json');
        if (File::exists($statusPath)) {
            $backupStatus = json_decode(File::get($statusPath), true);
        }

        return view('app.dashboard', ['year' => $year, 'students' => Student::where('status', 'active')->count(), 'todayCases' => ViolationCase::whereDate('occurred_at', today())->count(), 'monthCases' => ViolationCase::whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])->count(), 'openCases' => ViolationCase::whereIn('status', ['open', 'in_follow_up'])->count(), 'followUps' => FollowUp::with('case.student')->whereNull('completed_at')->orderBy('scheduled_at')->limit(6)->get(), 'topInstruments' => ViolationInstrument::query()->select('violation_instruments.name')->selectRaw('COUNT(case_items.id) as total')->join('case_items', 'case_items.instrument_id', '=', 'violation_instruments.id')->groupBy('violation_instruments.id', 'violation_instruments.name')->orderByDesc('total')->limit(5)->get(), 'priority' => $priority, 'backupStatus' => $backupStatus, 'severityCounts' => $severityCounts, 'classDistribution' => $classDistribution, 'recentActivities' => AuditLog::with('user')->latest()->limit(8)->get()]);
    }
}
