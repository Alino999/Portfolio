<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResumeTemplateCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(ResumeTemplate::class, 'category_id');
    }
}
