<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'banner_url',
        'meta_title',
        'meta_description',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];
}
