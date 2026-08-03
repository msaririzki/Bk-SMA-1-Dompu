<?php

namespace App\Models;

use App\Enums\ImportRowStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    protected $fillable = ['batch_id', 'sheet_name', 'row_number', 'raw_payload', 'normalized_payload', 'status', 'matched_student_id', 'message'];

    protected function casts(): array
    {
        return ['raw_payload' => 'array', 'normalized_payload' => 'array', 'status' => ImportRowStatus::class];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function matchedStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'matched_student_id');
    }
}
