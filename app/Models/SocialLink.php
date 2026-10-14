<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLink extends Model
{
    protected $fillable = ['profile_id','platform','label','url','icon','image_path','is_active','sort_order'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
