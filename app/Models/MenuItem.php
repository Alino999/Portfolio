<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['parent_id','label','url','sort_order','is_active'];

    protected $casts = [
        'label' => 'array',
        'is_active' => 'boolean',
    ];
}
