<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationCategory extends Model
{
    protected $fillable = ['code', 'name', 'sort_order'];

    public function instruments(): HasMany
    {
        return $this->hasMany(ViolationInstrument::class, 'category_id');
    }
}
