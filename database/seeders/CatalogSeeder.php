<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Seed demo categories, products, and product images.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Apparel',
                'description' => 'Everyday clothing and accessories.',
            ],
            [
                'name' => 'Home and Living',
                'description' => 'Useful items for home and everyday life.',
            ],
        ];

        foreach ($categories as $attributes) {
            $category = Category::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($attributes['name'])])
                ->first();

            if ($category === null) {
                $category = new Category;
            }

            $category->fill($attributes);
            $category->save();
        }

        $products = [
            [
                'category_name' => 'Apparel',
                'name' => 'Everyday Tee',
                'description' => 'A comfortable cotton t-shirt for daily wear.',
                'price' => '24.50',
                'stock_quantity' => 20,
                'status' => 'active',
                'image_public_id' => 'seed-products/everyday-tee',
                'image_url' => 'https://placehold.co/600x600/png?text=Everyday+Tee',
            ],
            [
                'category_name' => 'Apparel',
                'name' => 'Canvas Tote',
                'description' => 'A reusable canvas tote for everyday errands.',
                'price' => '18.00',
                'stock_quantity' => 30,
                'status' => 'active',
                'image_public_id' => 'seed-products/canvas-tote',
                'image_url' => 'https://placehold.co/600x600/png?text=Canvas+Tote',
            ],
            [
                'category_name' => 'Home and Living',
                'name' => 'Ceramic Mug',
                'description' => 'A sturdy ceramic mug for hot and cold drinks.',
                'price' => '12.00',
                'stock_quantity' => 25,
                'status' => 'active',
                'image_public_id' => 'seed-products/ceramic-mug',
                'image_url' => 'https://placehold.co/600x600/png?text=Ceramic+Mug',
            ],
        ];

        foreach ($products as $attributes) {
            $category = Category::query()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($attributes['category_name'])])
                ->firstOrFail();

            $product = Product::query()->firstOrNew([
                'category_id' => $category->id,
                'name' => $attributes['name'],
            ]);
            $product->fill([
                'description' => $attributes['description'],
                'price' => $attributes['price'],
                'stock_quantity' => $attributes['stock_quantity'],
                'status' => $attributes['status'],
            ]);
            $product->save();

            ProductImage::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'image_public_id' => $attributes['image_public_id'],
                ],
                [
                    'image_url' => $attributes['image_url'],
                    'is_primary' => true,
                    'sort_order' => 1,
                ],
            );
        }
    }
}
