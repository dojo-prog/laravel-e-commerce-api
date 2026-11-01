<?php

namespace App\Actions\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class RetryPayment
{
    public function __construct(
        private readonly ProcessPayment $processPayment,
    ) {}

    public function handle(Order $order): Payment
    {
        $payment = DB::transaction(function () use ($order): Payment {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'pending') {
                throw new ConflictHttpException(
                    'Payments can only be retried while the order is pending.'
                );
            }

            $latestPayment = $lockedOrder->payments()
                ->reorder()
                ->orderByDesc('attempt_number')
                ->lockForUpdate()
                ->first();

            if ($latestPayment === null) {
                throw new ConflictHttpException(
                    'This order does not have a payment attempt to retry.'
                );
            }

            if ($latestPayment->status === 'pending') {
                return $latestPayment;
            }

            if ($latestPayment->status !== 'failed') {
                throw new ConflictHttpException(
                    'Only failed payments can be retried.'
                );
            }

            $nextAttemptNumber = $latestPayment->attempt_number + 1;
            $payment = new Payment([
                'payment_reference' => 'pending-'.$lockedOrder->id.'-'.$nextAttemptNumber,
                'method' => $latestPayment->method,
                'status' => 'pending',
                'amount' => $lockedOrder->total_amount,
            ]);
            $payment->attempt_number = $nextAttemptNumber;
            $lockedOrder->payments()->save($payment);

            return $payment;
        });

        return $this->processPayment->handle($payment);
    }
}
