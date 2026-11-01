<?php

namespace App\Contracts\Payments;

use App\Data\Payments\PaymentGatewayResult;
use App\Models\Payment;

interface PaymentGateway
{
    public function process(Payment $payment): PaymentGatewayResult;
}
