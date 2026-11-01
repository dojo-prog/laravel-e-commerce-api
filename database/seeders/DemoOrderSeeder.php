<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoOrderSeeder extends Seeder
{
    /**
     * Seed a deterministic confirmed demo order and paid mock payment.
     */
    public function run(): void
    {
        $customer = User::query()
            ->where('email', 'demo.customer@dojo.com')
            ->firstOrFail();
        $address = $customer->addresses()
            ->where('address_line_1', '123 Demo Street')
            ->firstOrFail();
        $tee = Product::query()->where('name', 'Everyday Tee')->firstOrFail();
        $mug = Product::query()->where('name', 'Ceramic Mug')->firstOrFail();

        $lineItems = [
            [$tee, 2],
            [$mug, 1],
        ];
        $subtotalCents = 0;

        foreach ($lineItems as [$product, $quantity]) {
            $subtotalCents += $this->amountInCents($product->price) * $quantity;
        }

        $subtotal = $this->formatAmount($subtotalCents);
        $notes = 'Seeded demo order.';
        $order = Order::query()
            ->where('user_id', $customer->id)
            ->where('notes', $notes)
            ->first();

        if ($order === null) {
            $order = new Order;
            $order->user_id = $customer->id;
            $order->notes = $notes;
        }

        $order->status = 'confirmed';
        $order->subtotal = $subtotal;
        $order->shipping_fee = '0.00';
        $order->discount_amount = '0.00';
        $order->total_amount = $subtotal;
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
        $order->shipping_address_snapshot = $addressSnapshot;
        $order->save();

        foreach ($lineItems as [$product, $quantity]) {
            $lineSubtotalCents = $this->amountInCents($product->price) * $quantity;
            $orderItem = $order->orderItems()->firstOrNew([
                'product_id' => $product->id,
            ]);
            $orderItem->fill([
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $this->formatAmount($lineSubtotalCents),
            ]);
            $orderItem->save();
        }

        $this->seedStatusHistory($order, $customer);
        $this->seedPayment($order);
    }

    private function seedStatusHistory(Order $order, User $customer): void
    {
        $initialHistory = $order->statusHistories()->firstOrNew([
            'previous_status' => null,
            'new_status' => 'pending',
        ]);
        $initialHistory->notes = 'Order placed.';
        $initialHistory->user_id = $customer->id;
        $initialHistory->save();

        $confirmedHistory = $order->statusHistories()->firstOrNew([
            'previous_status' => 'pending',
            'new_status' => 'confirmed',
        ]);
        $confirmedHistory->notes = 'Payment completed.';
        $confirmedHistory->user_id = $customer->id;
        $confirmedHistory->save();
    }

    private function seedPayment(Order $order): void
    {
        $payment = $order->payments()->firstOrNew(['attempt_number' => 1]);
        $payment->attempt_number = 1;
        $payment->payment_reference = 'seed-'.$order->id;
        $payment->method = 'mock';
        $payment->status = 'paid';
        $payment->amount = $order->total_amount;
        $payment->paid_at ??= now();
        $payment->save();
    }

    private function amountInCents(string|float $amount): int
    {
        return (int) str_replace('.', '', number_format((float) $amount, 2, '.', ''));
    }

    private function formatAmount(int $amountInCents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($amountInCents, 100),
            $amountInCents % 100,
        );
    }
}
