<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}