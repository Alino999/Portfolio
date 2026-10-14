<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArticleCategory extends Model
{
    protected $fillable = ['name', 'slug', 'sort_order', 'published'];

    protected $casts = [
        'name' => 'array',
        'published' => 'boolean',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('sort_order');
    }
}
