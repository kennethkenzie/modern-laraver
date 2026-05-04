<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_number',
        'profile_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'fulfillment_method',
        'payment_method',
        'status',
        'payment_status',
        'subtotal',
        'shipping_total',
        'total',
        'currency_code',
        'address',
        'city',
        'country',
        'pickup_location_id',
        'pickup_location_title',
        'metadata',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}


