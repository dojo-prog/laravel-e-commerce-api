<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCustomerSeeder extends Seeder
{
    /**
     * Seed a demo customer, address, and cart.
     */
    public function run(): void
    {
        $customer = User::query()->whereRaw(
            'LOWER(email) = ?',
            ['demo.customer@dojo.com'],
        )->first();

        if ($customer === null) {
            $customer = new User([
                'email' => 'demo.customer@dojo.com',
                'password' => Str::random(48),
            ]);
        }

        $customer->name = 'Demo Customer';
        $customer->role = 'customer';
        $customer->email_verified_at ??= now();
        $customer->save();

        $cart = Cart::query()->firstOrCreate(['user_id' => $customer->id]);
        $product = Product::query()->where('name', 'Canvas Tote')->firstOrFail();
        $cartItem = CartItem::query()->firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
        ]);
        $cartItem->quantity = 1;
        $cartItem->save();

        $address = Address::query()->firstOrNew([
            'user_id' => $customer->id,
            'address_line_1' => '123 Demo Street',
        ]);
        $address->fill([
            'recipient_name' => $customer->name,
            'phone' => '+639171234567',
            'address_line_2' => null,
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'postal_code' => '6000',
            'country' => 'Philippines',
            'is_default' => true,
        ]);
        $address->save();
    }
}
