<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeVisit extends Model
{
    use HasUuids;

    protected $fillable = ['document_id', 'counselee_name', 'class_name', 'gender', 'address', 'parent_name', 'problem', 'purpose', 'visit_date', 'met_with', 'result', 'follow_up', 'counselor_name', 'counselor_nip', 'homeroom_name', 'homeroom_nip', 'coordinator_name', 'coordinator_nip', 'place'];

    protected function casts(): array
    {
        return ['visit_date' => 'date'];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(SchoolDocument::class, 'document_id');
    }
}
