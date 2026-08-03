<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasUuids;

    protected $fillable = ['uploaded_by', 'academic_year_id', 'original_name', 'stored_path', 'file_hash', 'status', 'total_rows', 'ready_rows', 'conflict_rows', 'invalid_rows', 'imported_rows', 'committed_at'];

    protected function casts(): array
    {
        return ['committed_at' => 'datetime'];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportRow::class, 'batch_id');
    }
}
