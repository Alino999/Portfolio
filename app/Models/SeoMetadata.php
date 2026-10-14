<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata extends Model
{
    protected $fillable = ['model_type','model_id','title','description','keywords','canonical_url','og_image','robots'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'keywords' => 'array',
    ];
}
