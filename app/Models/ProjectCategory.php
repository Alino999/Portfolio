<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    protected $fillable = ['name','slug','sort_order','published'];

    protected $casts = [
        'name' => 'array',
        'published' => 'boolean',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('sort_order');
    }
}
