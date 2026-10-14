<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    protected $casts = [
        'name' => 'array',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class);
    }
}
