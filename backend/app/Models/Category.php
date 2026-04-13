<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_url',
        'is_active',
        'featured_on_home',
        'featured_sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'featured_on_home' => 'boolean',
        'featured_sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function homeGroupItems(): HasMany
    {
        return $this->hasMany(HomeCategoryGroupItem::class);
    }
}
