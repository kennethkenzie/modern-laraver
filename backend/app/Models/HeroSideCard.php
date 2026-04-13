<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeroSideCard extends Model
{
    use HasUuids;

    protected $fillable = [
        'eyebrow',
        'title',
        'image_url',
        'href',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
