<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $fillable = ['article_category_id','title','slug','excerpt','content','image_path','author_name','published_at','status','sort_order','featured','source_url'];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ArticleLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class);
    }
}
