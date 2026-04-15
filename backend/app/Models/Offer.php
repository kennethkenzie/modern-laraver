<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'headline',
        'description',
        'badge_text',
        'banner_image',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'starts_at',
        'expires_at',
        'usage_limit',
        'usage_count',
        'is_active',
        'is_featured',
        'target_type',
    ];

    protected $casts = [
        'value'              => 'decimal:2',
        'min_order_amount'   => 'decimal:2',
        'max_discount_amount'=> 'decimal:2',
        'starts_at'          => 'datetime',
        'expires_at'         => 'datetime',
        'usage_limit'        => 'integer',
        'usage_count'        => 'integer',
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'offer_products');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'offer_categories');
    }
}
