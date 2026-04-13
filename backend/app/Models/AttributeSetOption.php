<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeSetOption extends Model
{
    use HasUuids;

    protected $fillable = [
        'attribute_set_id',
        'value',
        'color_hex',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function attributeSet(): BelongsTo
    {
        return $this->belongsTo(AttributeSet::class);
    }
}
