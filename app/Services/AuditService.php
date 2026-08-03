<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(string $action, ?Model $subject = null, ?array $before = null, ?array $after = null, ?string $reason = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(), 'action' => $action,
            'subject_type' => $subject?->getMorphClass(), 'subject_id' => $subject?->getKey(),
            'before' => $before, 'after' => $after, 'reason' => $reason,
            'ip_address' => request()?->ip(), 'user_agent' => request()?->userAgent(),
        ]);
    }
}
