<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skill extends Model
{
    protected $fillable = [
        'skill_category_id',
        'name',
        'level',
        'level_en',
        'percentage',
        'years_experience',
        'description',
        'icon',
        'image_path',
        'sort_order',
        'published',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }
}
