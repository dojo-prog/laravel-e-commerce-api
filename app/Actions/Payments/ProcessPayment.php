<?php

namespace App\Actions\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProcessPayment
{
    public function __construct(
        private readonly PaymentGateway $paymentGateway,
    ) {}

    public function handle(Payment $payment): Payment
    {
        if ($payment->status !== 'pending') {
            return $payment->loadMissing('order');
        }

        $result = $this->paymentGateway->process($payment);

        return DB::transaction(function () use ($payment, $result): Payment {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status !== 'pending') {
                return $lockedPayment->load('order');
            }

            $order = Order::query()
                ->whereKey($lockedPayment->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== 'pending') {
                throw new ConflictHttpException(
                    'A payment cannot be processed for an order that is no longer pending.'
                );
            }

            $lockedPayment->forceFill([
                'payment_reference' => $result->reference,
                'status' => $result->successful ? 'paid' : 'failed',
                'paid_at' => $result->paidAt,
            ])->save();

            if ($result->successful) {
                $order->status = 'confirmed';
                $order->save();

                $statusHistory = $order->statusHistories()->make([
                    'previous_status' => 'pending',
                    'new_status' => 'confirmed',
                    'notes' => 'Payment completed.',
                ]);
                $statusHistory->user_id = $order->user_id;
                $statusHistory->save();
            }

            return $lockedPayment->load('order');
        });
    }
}
