<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    use HasUuids;

    protected $fillable = ['case_id', 'student_id', 'uploaded_by', 'disk', 'path', 'thumbnail_path', 'original_name', 'mime_type', 'size', 'description'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ViolationCase::class, 'case_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}
