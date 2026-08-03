<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['slug', 'title', 'content', 'is_published', 'updated_by'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
