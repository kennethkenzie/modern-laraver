<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'store_id',
        'category_id',
        'slug',
        'name',
        'short_description',
        'description',
        'brand',
        'currency_code',
        'list_price',
        'sale_price',
        'average_rating',
        'rating_count',
        'bestseller_label',
        'bestseller_category',
        'bought_past_month_label',
        'shipping_label',
        'in_stock_label',
        'delivery_label',
        'returns_label',
        'payment_label',
        'is_published',
        'is_featured_home',
        'home_sort_order',
        'published_at',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'rating_count' => 'integer',
        'is_published' => 'boolean',
        'is_featured_home' => 'boolean',
        'home_sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function specs(): HasMany
    {
        return $this->hasMany(ProductSpec::class)->orderBy('sort_order');
    }

    public function bullets(): HasMany
    {
        return $this->hasMany(ProductBullet::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(ProductRelation::class);
    }
}
