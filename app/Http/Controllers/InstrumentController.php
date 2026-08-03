<?php

namespace App\Http\Controllers;

use App\Models\SeverityLevel;
use App\Models\ViolationCategory;
use App\Models\ViolationInstrument;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstrumentController extends Controller
{
    public function index()
    {
        return view('app.instruments.index', ['categories' => ViolationCategory::with(['instruments' => fn ($q) => $q->orderBy('sort_order')])->orderBy('sort_order')->get(), 'levels' => SeverityLevel::orderBy('min_points')->get()]);
    }

    public function store(Request $r, AuditService $audit)
    {
        $d = $r->validate(['category_id' => 'required|exists:violation_categories,id', 'code' => 'required|string|max:20|unique:violation_instruments,code', 'name' => 'required|string', 'points' => 'required|integer|min:1|max:1000', 'sanction' => 'nullable|string']);
        $instrument = ViolationInstrument::create($d + ['is_active' => true, 'sort_order' => ViolationInstrument::max('sort_order') + 1]);
        $audit->record('instrument.created', $instrument, null, $instrument->toArray());

        return back()->with('success', 'Instrumen ditambahkan.');
    }

    public function update(Request $r, ViolationInstrument $instrument, AuditService $audit)
    {
        $d = $r->validate(['name' => 'required|string', 'points' => 'required|integer|min:1|max:1000', 'sanction' => 'nullable|string', 'is_active' => 'nullable|boolean']);
        $before = $instrument->toArray();
        $d['is_active'] = $r->boolean('is_active');
        $instrument->update($d);
        $audit->record('instrument.updated', $instrument, $before, $instrument->fresh()->toArray());

        return back()->with('success', 'Instrumen diperbarui. Data kasus lama tetap menggunakan snapshot.');
    }

    public function severities(Request $r, AuditService $audit)
    {
        $rows = $r->validate(['levels' => 'required|array|min:1', 'levels.*.id' => 'required|exists:severity_levels,id', 'levels.*.min_points' => 'required|integer|min:1', 'levels.*.max_points' => 'nullable|integer', 'levels.*.name' => 'required|string|max:50']);
        $sorted = collect($rows['levels'])->sortBy('min_points')->values();
        $previousMax = null;
        foreach ($sorted as $index => $row) {
            if ($row['max_points'] !== null && (int) $row['max_points'] < (int) $row['min_points']) {
                return back()->withErrors(['levels' => 'Nilai maksimum tidak boleh lebih kecil dari nilai minimum.']);
            }if ($index > 0 && ($previousMax === null || (int) $row['min_points'] <= (int) $previousMax)) {
                return back()->withErrors(['levels' => 'Rentang kategori tidak boleh bertumpang tindih dan rentang terbuka harus berada paling akhir.']);
            }$previousMax = $row['max_points'];
        }$before = SeverityLevel::orderBy('min_points')->get()->toArray();
        DB::transaction(fn () => $sorted->each(fn ($row) => SeverityLevel::find($row['id'])->update(['name' => $row['name'], 'min_points' => $row['min_points'], 'max_points' => $row['max_points']])));
        $audit->record('severity_levels.updated', null, $before, SeverityLevel::orderBy('min_points')->get()->toArray());

        return back()->with('success','Kategori skor diperbarui.');
    }
}
