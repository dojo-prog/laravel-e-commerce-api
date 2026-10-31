<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class ProductImage extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'image_url',
        'image_public_id',
        'is_primary',
        'sort_order'
    ];

    #[Override]
    protected function casts()
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer'
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
