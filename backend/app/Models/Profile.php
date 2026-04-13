<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Profile extends Model implements AuthenticatableContract
{
    use HasUuids, Authenticatable, HasApiTokens;

    protected $fillable = [
        'id',
        'store_id',
        'email',
        'full_name',
        'phone',
        'password',
        'role',
        'avatar_url',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'role' => 'string',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }
}
