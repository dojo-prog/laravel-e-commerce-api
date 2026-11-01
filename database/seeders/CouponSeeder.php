<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Seed repeatable coupon examples.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => '10.00',
                'minimum_order_amount' => '20.00',
                'maximum_discount' => '25.00',
                'usage_limit' => 100,
                'starts_at' => now()->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'is_active' => true,
            ],
            [
                'code' => 'SAVE5',
                'type' => 'fixed',
                'value' => '5.00',
                'minimum_order_amount' => '30.00',
                'maximum_discount' => '5.00',
                'usage_limit' => 50,
                'starts_at' => now()->toDateString(),
                'expires_at' => now()->addYear()->toDateString(),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $attributes) {
            $coupon = Coupon::query()
                ->whereRaw('LOWER(code) = ?', [mb_strtolower($attributes['code'])])
                ->first();

            if ($coupon === null) {
                $coupon = new Coupon;
                $coupon->usage_count = 0;
            }

            $coupon->fill($attributes);
            $coupon->save();
        }
    }
}
