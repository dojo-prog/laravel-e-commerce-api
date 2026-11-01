<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Data\Payments\PaymentGatewayResult;
use App\Models\Payment;
use UnexpectedValueException;

class MockPaymentGateway implements PaymentGateway
{
    public function process(Payment $payment): PaymentGatewayResult
    {
        $outcome = config('services.mock_payment.outcome', 'paid');

        if (! in_array($outcome, ['paid', 'failed'], true)) {
            throw new UnexpectedValueException(
                'The configured mock payment outcome must be paid or failed.'
            );
        }

        return new PaymentGatewayResult(
            reference: 'mock-'.$payment->id,
            successful: $outcome === 'paid',
            paidAt: $outcome === 'paid' ? now() : null,
        );
    }
}
