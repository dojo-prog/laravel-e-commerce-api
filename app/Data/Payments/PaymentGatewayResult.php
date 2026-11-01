<?php

namespace App\Data\Payments;

use DateTimeInterface;
use InvalidArgumentException;

final readonly class PaymentGatewayResult
{
    public function __construct(
        public string $reference,
        public bool $successful,
        public ?DateTimeInterface $paidAt,
    ) {
        if ($reference === '') {
            throw new InvalidArgumentException('A payment gateway reference is required.');
        }

        if ($successful && $paidAt === null) {
            throw new InvalidArgumentException('A successful payment must have a paid timestamp.');
        }

        if (! $successful && $paidAt !== null) {
            throw new InvalidArgumentException('A failed payment cannot have a paid timestamp.');
        }
    }
}
