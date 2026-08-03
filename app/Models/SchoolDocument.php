<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolDocument extends Model
{
    use HasUuids;

    protected $table = 'documents';

    protected $fillable = ['student_id', 'case_id', 'academic_year_id', 'created_by', 'type', 'number', 'document_date', 'status', 'payload'];

    protected function casts(): array
    {
        return ['type' => DocumentType::class, 'document_date' => 'date', 'payload' => 'array'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(ViolationCase::class, 'case_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function homeVisit(): HasOne
    {
        return $this->hasOne(HomeVisit::class, 'document_id');
    }
}
