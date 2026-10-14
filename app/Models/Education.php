<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'profile_id',
        'school',
        'degree',
        'degree_en',
        'field',
        'field_en',
        'start_date',
        'end_date',
        'description',
        'image_path',
        'sort_order',
        'published',
    ];

    protected $casts = [
        'description' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'published' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
