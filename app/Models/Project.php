<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = ['project_category_id','title','slug','short_description','description','image_path','gallery','project_date','client','role','technologies','features','results','demo_url','github_url','documentation_url','status','is_confidential','project_type','sort_order','published'];

    protected $casts = [
        'title' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'gallery' => 'array',
        'technologies' => 'array',
        'features' => 'array',
        'results' => 'array',
        'project_date' => 'date',
        'is_confidential' => 'boolean',
        'published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
