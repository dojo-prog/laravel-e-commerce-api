<?php

namespace App\Actions\Checkout;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PlaceOrder
{
    private const MAX_AMOUNT_CENTS = 99_999_999;

    /**
     * @param  array{
     *     address_id: string,
     *     payment_method: string,
     *     idempotency_key: string,
     *     coupon_code?: string|null,
     *     notes?: string|null
     * }  $data
     */
    public function handle(User $user, array $data): Order
    {
        $checkout = [
            'address_id' => $data['address_id'],
            'coupon_code' => isset($data['coupon_code'])
                ? Str::lower(trim($data['coupon_code']))
                : null,
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
        ];
        $requestHash = hash('sha256', json_encode($checkout, JSON_THROW_ON_ERROR));
        $idempotencyKey = $data['idempotency_key'];

        return DB::transaction(function () use ($user, $checkout, $idempotencyKey, $requestHash): Order {
            $existingOrder = $this->findIdempotentOrder($user, $idempotencyKey, $requestHash);

            if ($existingOrder !== null) {
                return $this->loadOrder($existingOrder);
            }

            $cart = $user->cart()->lockForUpdate()->first();

            if ($cart === null) {
                throw new ConflictHttpException('Your cart is not available for checkout.');
            }

            $existingOrder = $this->findIdempotentOrder($user, $idempotencyKey, $requestHash);

            if ($existingOrder !== null) {
                return $this->loadOrder($existingOrder);
            }

            $address = $user->addresses()
                ->whereKey($checkout['address_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $cartItems = $cart->cartItems()
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotalCents = 0;
            $lineItems = [];

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if ($product === null || $product->status !== 'active') {
                    throw ValidationException::withMessages([
                        'cart' => 'One or more products in your cart are no longer available.',
                    ]);
                }

                if ($cartItem->quantity < 1 || $product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "There is not enough stock for {$product->name}.",
                    ]);
                }

                $unitPriceCents = $this->toCents($product->price);
                $lineSubtotalCents = $unitPriceCents * $cartItem->quantity;

                if (
                    $lineSubtotalCents > self::MAX_AMOUNT_CENTS
                    || $subtotalCents > self::MAX_AMOUNT_CENTS - $lineSubtotalCents
                ) {
                    throw ValidationException::withMessages([
                        'cart' => 'The checkout total exceeds the supported order amount.',
                    ]);
                }

                $subtotalCents += $lineSubtotalCents;

                $lineItems[] = [
                    'cart_item' => $cartItem,
                    'product' => $product,
                    'unit_price_cents' => $unitPriceCents,
                    'subtotal_cents' => $lineSubtotalCents,
                ];
            }

            $coupon = $checkout['coupon_code'] === null
                ? null
                : $this->findEligibleCoupon($checkout['coupon_code'], $subtotalCents);
            $discountCents = $coupon === null
                ? 0
                : $this->calculateDiscount($coupon, $subtotalCents);
            $shippingFeeCents = 0;
            $totalCents = $subtotalCents - $discountCents + $shippingFeeCents;
            $this->ensureAmountFitsColumn($totalCents);

            $addressSnapshot = $address->only([
                'recipient_name',
                'phone',
                'address_line_1',
                'address_line_2',
                'city',
                'province',
                'postal_code',
                'country',
            ]);
            $addressSnapshot['address_id'] = $address->id;

            $order = $user->orders()->create([
                'status' => 'pending',
                'subtotal' => $this->fromCents($subtotalCents),
                'shipping_fee' => $this->fromCents($shippingFeeCents),
                'discount_amount' => $this->fromCents($discountCents),
                'total_amount' => $this->fromCents($totalCents),
                'shipping_address_snapshot' => $addressSnapshot,
                'notes' => $checkout['notes'],
                'idempotency_key' => $idempotencyKey,
                'checkout_request_hash' => $requestHash,
            ]);

            $initialStatusHistory = $order->statusHistories()->make([
                'previous_status' => null,
                'new_status' => 'pending',
                'notes' => 'Order placed.',
            ]);
            $initialStatusHistory->user_id = $user->id;
            $initialStatusHistory->save();

            foreach ($lineItems as $lineItem) {
                /** @var Product $product */
                $product = $lineItem['product'];
                $cartItem = $lineItem['cart_item'];

                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $this->fromCents($lineItem['unit_price_cents']),
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $this->fromCents($lineItem['subtotal_cents']),
                ]);

                $product->stock_quantity -= $cartItem->quantity;
                $product->save();
            }

            if ($coupon !== null) {
                $coupon->couponUsages()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $this->fromCents($discountCents),
                ]);

                $coupon->usage_count++;
                $coupon->save();
            }

            $payment = new Payment([
                'payment_reference' => 'pending-'.$order->id,
                'method' => $checkout['payment_method'],
                'status' => 'pending',
                'amount' => $this->fromCents($totalCents),
            ]);
            $payment->attempt_number = 1;
            $order->payments()->save($payment);

            $cart->cartItems()->delete();

            return $this->loadOrder($order);
        }, attempts: 3);
    }

    private function findIdempotentOrder(User $user, string $idempotencyKey, string $requestHash): ?Order
    {
        $order = $user->orders()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (
            $order !== null
            && ! hash_equals((string) $order->checkout_request_hash, $requestHash)
        ) {
            throw new ConflictHttpException(
                'This idempotency key was already used for a different checkout request.'
            );
        }

        return $order;
    }

    private function findEligibleCoupon(string $code, int $subtotalCents): Coupon
    {
        $coupon = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [$code])
            ->lockForUpdate()
            ->first();

        $today = now()->toDateString();

        if (
            $coupon === null
            || ! $coupon->is_active
            || ($coupon->starts_at !== null && $coupon->starts_at->toDateString() > $today)
            || ($coupon->expires_at !== null && $coupon->expires_at->toDateString() < $today)
            || ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit)
        ) {
            throw ValidationException::withMessages([
                'coupon_code' => 'The selected coupon is invalid or unavailable.',
            ]);
        }

        if (
            $coupon->minimum_order_amount !== null
            && $subtotalCents < $this->toCents($coupon->minimum_order_amount)
        ) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Your cart does not meet the minimum amount for this coupon.',
            ]);
        }

        return $coupon;
    }

    private function calculateDiscount(Coupon $coupon, int $subtotalCents): int
    {
        $valueCents = $this->toCents($coupon->value);

        $discountCents = match ($coupon->type) {
            'fixed' => $valueCents,
            'percentage' => intdiv(($subtotalCents * $valueCents) + 5000, 10000),
            default => throw ValidationException::withMessages([
                'coupon_code' => 'The selected coupon type is not supported.',
            ]),
        };

        if ($coupon->maximum_discount !== null) {
            $discountCents = min($discountCents, $this->toCents($coupon->maximum_discount));
        }

        return min($discountCents, $subtotalCents);
    }

    private function loadOrder(Order $order): Order
    {
        return $order->load([
            'orderItems.product',
            'payments',
            'statusHistories',
        ]);
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }

    private function ensureAmountFitsColumn(int $amount): void
    {
        if ($amount > self::MAX_AMOUNT_CENTS) {
            throw ValidationException::withMessages([
                'cart' => 'The checkout total exceeds the supported order amount.',
            ]);
        }
    }
}
