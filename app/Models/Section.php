<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['key','title','enabled','sort_order'];

    protected $casts = [
        'title' => 'array',
        'enabled' => 'boolean',
    ];
}
