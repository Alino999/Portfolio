<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    protected $fillable = ['name','category','icon','image_path','sort_order','published'];

    protected $casts = [
        'name' => 'array',
        'published' => 'boolean',
    ];
}
