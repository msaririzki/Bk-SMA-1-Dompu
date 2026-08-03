<?php

namespace App\Models;

use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['temporary_id', 'nis', 'nisn', 'name', 'normalized_name', 'gender', 'status', 'photo_path'];

    protected function casts(): array
    {
        return ['status' => StudentStatus::class];
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(StudentAlias::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function cases(): HasMany
    {
        return $this->hasMany(ViolationCase::class);
    }

    public function account(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function currentEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class)->ofMany('id', 'max');
    }

    public function pointsForYear(int $academicYearId): int
    {
        return (int) CaseItem::query()->whereHas('case', fn ($q) => $q
            ->where('student_id', $this->id)->where('academic_year_id', $academicYearId)
            ->where('status', '!=', 'cancelled'))->sum('points');
    }

    public function allTimePoints(): int
    {
        return (int) CaseItem::query()->whereHas('case', fn ($q) => $q
            ->where('student_id', $this->id)->where('status', '!=', 'cancelled'))->sum('points');
    }
}
