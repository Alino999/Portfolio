<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experience extends Model
{
    protected $fillable = [
        'profile_id',
        'company',
        'company_en',
        'position',
        'position_en',
        'location',
        'contract_type',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'responsibilities',
        'achievements',
        'technologies',
        'logo_path',
        'image_path',
        'website',
        'sort_order',
        'published',
    ];

    protected $casts = [
        'description' => 'array',
        'responsibilities' => 'array',
        'achievements' => 'array',
        'technologies' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'published' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
