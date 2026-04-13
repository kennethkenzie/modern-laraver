<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeCategoryGroup extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'cta_label',
        'cta_href',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(HomeCategoryGroupItem::class, 'group_id');
    }
}
