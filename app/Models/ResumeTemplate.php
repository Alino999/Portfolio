<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeTemplate extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'layout',
        'style',
        'ats_friendly',
        'languages',
        'recommended_pages',
        'available_formats',
        'available_colors',
        'blocks',
        'preview_image',
        'is_active',
        'is_system',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'ats_friendly' => 'boolean',
        'languages' => 'array',
        'available_formats' => 'array',
        'available_colors' => 'array',
        'blocks' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResumeTemplateCategory::class, 'category_id');
    }

    public function supportsPhoto(): bool
    {
        if (is_array($this->blocks) && $this->blocks !== []) {
            return in_array('photo', $this->blocks, true);
        }

        return in_array($this->layout, ['portfolio-sidebar', 'warm-academic'], true);
    }
}
