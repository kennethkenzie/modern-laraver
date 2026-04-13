<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttributeSet extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'input_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AttributeSetOption::class)->orderBy('sort_order');
    }
}
