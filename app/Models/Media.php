<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['title','file_name','file_path','mime_type','file_size','category','alt_text','sort_order','is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];
}
