<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Override;

class Product extends Model
{
    use HasUuids;

    #[Override]
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->sku = self::generateSku($product);
        });
    }

    protected static function generateSku(Product $product): string
    {
        $category = Category::find($product->category_id);
        $categoryName = $category ? $category->name : 'GEN';

        $categoryCode = strtoupper(substr($categoryName, 0, 3));
        $nameCode = strtoupper(substr($product->name, 0, 3));
        $uniqueSuffix = strtoupper(Str::random(4));

        return "PRD-{$nameCode}-{$categoryCode}-{$uniqueSuffix}";
    }

    protected $fillable = [
        'category_id',
        'description',
        'price',
        'stock_quantity',
        'status',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_items')
            ->using(CartItem::class)
            ->withPivot(['id', 'quantity'])
            ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->using(OrderItem::class)
            ->withPivot([
                'id',
                'product_name',
                'product_sku',
                'unit_price',
                'quantity',
                'subtotal',
            ])
            ->withTimestamps();
    }
}
