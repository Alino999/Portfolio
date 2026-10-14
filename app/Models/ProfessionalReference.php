<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalReference extends Model
{
    protected $fillable = [
        'profile_id',
        'name',
        'role',
        'role_en',
        'company',
        'email',
        'phone',
        'image_path',
        'sort_order',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
