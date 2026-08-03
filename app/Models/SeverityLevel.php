<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeverityLevel extends Model
{
    protected $fillable = ['name', 'min_points', 'max_points', 'color', 'recommended_action', 'sort_order'];

    public function contains(int $points): bool
    {
        return $points >= $this->min_points && ($this->max_points === null || $points <= $this->max_points);
    }

    public static function forPoints(int $points): ?self
    {
        return static::orderBy('min_points')->get()->first(fn (self $level) => $level->contains($points));
    }
}
