<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CmsPage;
use App\Models\SchoolSetting;
use App\Models\SeverityLevel;
use App\Models\Student;
use App\Models\ViolationCase;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    public function publicPage(string $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('public.page', compact('page'));
    }

    public function home()
    {
        $year = AcademicYear::active();
        $priorityCounts = ['Ringan' => 0, 'Sedang' => 0, 'Berat' => 0];

        if ($year) {
            $levels = SeverityLevel::orderBy('min_points')->get();
            $annualPoints = DB::table('violation_cases')
                ->selectRaw('SUM(case_items.points) AS total_points')
                ->join('case_items', 'case_items.case_id', '=', 'violation_cases.id')
                ->where('violation_cases.academic_year_id', $year->id)
                ->where('violation_cases.status', '!=', 'cancelled')
                ->whereNull('violation_cases.deleted_at')
                ->groupBy('violation_cases.student_id')
                ->pluck('total_points');

            foreach ($annualPoints as $points) {
                $level = $levels->first(fn (SeverityLevel $level) => $level->contains((int) $points));
                $name = mb_strtolower($level?->name ?? 'berat');
                $bucket = match (true) {
                    str_contains($name, 'ringan') => 'Ringan',
                    str_contains($name, 'sedang') => 'Sedang',
                    default => 'Berat',
                };
                $priorityCounts[$bucket]++;
            }
        }

        $priorityTotal = array_sum($priorityCounts);
        $priorityColors = ['Ringan' => 'bg-teal-400', 'Sedang' => 'bg-amber-400', 'Berat' => 'bg-orange-400'];
        $priorities = collect($priorityCounts)->map(fn (int $count, string $label) => [
            'label' => $label,
            'count' => $count,
            'percentage' => $priorityTotal > 0 ? (int) round($count / $priorityTotal * 100) : 0,
            'color' => $priorityColors[$label],
        ])->values();

        return view('public.home', [
            'pages' => CmsPage::where('is_published', true)->get()->keyBy('slug'),
            'settings' => SchoolSetting::pluck('value', 'key'),
            'preview' => [
                'students' => Student::where('status', 'active')->count(),
                'month_cases' => ViolationCase::whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'open_cases' => ViolationCase::whereIn('status', ['open', 'in_follow_up'])->count(),
                'priorities' => $priorities,
            ],
        ]);
    }

    public function index()
    {
        return view('app.cms.index', ['pages' => CmsPage::orderBy('title')->get(), 'settings' => SchoolSetting::pluck('value', 'key')]);
    }

    public function edit(CmsPage $page)
    {
        return view('app.cms.edit', compact('page'));
    }

    public function update(Request $r, CmsPage $page, AuditService $audit)
    {
        $d = $r->validate(['title' => 'required|string|max:255', 'content' => 'required|string', 'is_published' => 'nullable|boolean']);
        $before = $page->toArray();
        $d['content'] = $this->sanitize($d['content']);
        $d['is_published'] = $r->boolean('is_published');
        $d['updated_by'] = $r->user()->id;
        $page->update($d);
        $audit->record('cms.page_updated', $page, $before, $page->fresh()->toArray());

        return back()->with('success', 'Konten diperbarui.');
    }

    public function settings(Request $r, AuditService $audit)
    {
        $d = $r->validate(['school_name' => 'required|string|max:150', 'tagline' => 'nullable|string|max:255', 'school_address' => 'nullable|string|max:500', 'school_phone' => 'nullable|string|max:50', 'school_email' => 'nullable|email|max:150', 'principal_name' => 'nullable|string|max:150', 'principal_nip' => 'nullable|string|max:50', 'coordinator_name' => 'nullable|string|max:150', 'coordinator_nip' => 'nullable|string|max:50', 'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096']);
        $before = SchoolSetting::pluck('value', 'key')->toArray();
        foreach ($d as $key => $value) {
            if ($key === 'logo') {
                continue;
            }SchoolSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']);
        }if ($r->hasFile('logo')) {
            $file = $r->file('logo');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->storeAs('cms', Str::uuid().'.'.$extension, 'public');
            $old = SchoolSetting::value('school_logo');
            SchoolSetting::updateOrCreate(['key' => 'school_logo'], ['value' => $path, 'type' => 'image']);
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        } $audit->record('cms.settings_updated', null, $before, SchoolSetting::pluck('value', 'key')->toArray());

        return back()->with('success', 'Identitas sekolah diperbarui.');
    }

    private function sanitize(string $html): string
    {
        $html = strip_tags($html, '<p><br><h2><h3><strong><em><ul><ol><li><blockquote>');

        return preg_replace('/<\s*([a-z0-9]+)\b[^>]*>/i', '<$1>', $html);
    }
}
