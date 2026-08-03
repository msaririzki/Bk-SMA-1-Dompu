<?php

namespace App\Models;

use App\Enums\FollowUpType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $fillable = ['case_id', 'created_by', 'type', 'scheduled_at', 'completed_at', 'parent_name', 'parent_contact', 'notes', 'status'];

    protected function casts(): array
    {
        return ['type' => FollowUpType::class, 'scheduled_at' => 'date', 'completed_at' => 'date'];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(ViolationCase::class, 'case_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
