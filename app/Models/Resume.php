<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    protected $fillable = [
        'profile_id',
        'title',
        'slug',
        'locale',
        'resume_template_id',
        'length',
        'format',
        'sections',
        'content_selection',
        'customization',
        'target_role',
        'target_market',
        'summary_override',
        'is_primary',
        'published',
    ];

    protected $casts = [
        'sections' => 'array',
        'content_selection' => 'array',
        'customization' => 'array',
        'is_primary' => 'boolean',
        'published' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResumeTemplate::class, 'resume_template_id');
    }
}
