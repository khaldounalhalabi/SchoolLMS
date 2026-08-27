<?php

namespace App\Data;

final readonly class PaymentCheckoutData
{
    public function __construct(
        public int|string|null $studentId,
        public string $successUrl,
        public string $cancelUrl,
    ) {}
}
