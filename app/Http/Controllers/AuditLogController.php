<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __invoke(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->q, fn ($query, $term) => $query->where(fn ($nested) => $nested
                ->where('action', 'like', "%{$term}%")
                ->orWhere('subject_type', 'like', "%{$term}%")
                ->orWhere('subject_id', 'like', "%{$term}%")))
            ->latest()->paginate(30)->withQueryString();

        return view('app.audit.index', compact('logs'));
    }
}
