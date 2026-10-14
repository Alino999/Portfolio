<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    protected $fillable = ['title','issuer','issuer_en','issue_date','expiration_date','credential_id','url','description','image_path','sort_order','published'];

    protected $casts = [
        'title' => 'array',
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'description' => 'array',
        'published' => 'boolean',
    ];
}
