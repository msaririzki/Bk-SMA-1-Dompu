<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViolationCase extends Model
{
    use HasUuids,SoftDeletes;

    protected $fillable = ['case_number', 'student_id', 'academic_year_id', 'created_by', 'occurred_at', 'location', 'chronology', 'status', 'cancellation_reason', 'resolved_at'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'resolved_at' => 'datetime', 'status' => CaseStatus::class];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CaseItem::class, 'case_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'case_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'case_id');
    }

    public function totalPoints(): int
    {
        return (int) $this->items->sum('points');
    }
}
