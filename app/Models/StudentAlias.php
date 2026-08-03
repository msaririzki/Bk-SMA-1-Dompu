<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAlias extends Model
{
    protected $fillable = ['student_id', 'name', 'normalized_name', 'source'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
