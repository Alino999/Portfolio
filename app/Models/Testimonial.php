<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['author','role','company','content','rating','sort_order','published','submitted_by_client'];

    protected $casts = [
        'content' => 'array',
        'published' => 'boolean',
        'submitted_by_client' => 'boolean',
    ];
}
