<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name','locale','level','level_en','image_path','sort_order','published'];

    protected $casts = [
        'name' => 'array',
        'published' => 'boolean',
    ];
}
