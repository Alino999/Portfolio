<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'description',
        'achievement_date',
        'url',
        'image_path',
        'sort_order',
        'published',
    ];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'achievement_date' => 'date',
        'published' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}