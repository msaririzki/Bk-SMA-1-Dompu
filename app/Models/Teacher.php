<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nip', 'name', 'phone', 'is_counselor'];

    protected function casts(): array
    {
        return ['is_counselor' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'counselor_assignments', 'teacher_id', 'class_id')->withTimestamps();
    }
}
