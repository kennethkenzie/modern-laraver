<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NavigationLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'kind',
        'label',
        'href',
        'icon',
        'badge_text',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
