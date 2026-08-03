<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViolationInstrument extends Model
{
    protected $fillable = ['category_id', 'code', 'name', 'points', 'sanction', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'points' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ViolationCategory::class, 'category_id');
    }
}
