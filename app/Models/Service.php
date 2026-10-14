<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['title','description','icon','image_path','sort_order','published'];

    protected $casts = [
        'title' => 'array',
        'description' => 'array',
        'published' => 'boolean',
    ];
}
