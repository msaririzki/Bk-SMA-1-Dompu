<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseItem extends Model
{
    protected $fillable = ['case_id', 'instrument_id', 'instrument_code', 'instrument_name', 'points', 'sanction_snapshot'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ViolationCase::class, 'case_id');
    }

    public function instrument(): BelongsTo
    {
        return $this->belongsTo(ViolationInstrument::class, 'instrument_id');
    }
}
