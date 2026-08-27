<?php

namespace App\Domain;

use App\Enums\PaymentCheckoutStatus;

readonly class PaymentCheckoutResult
{
    public function __construct(
        public PaymentCheckoutStatus $status,
        public ?string $url = null,
        public ?string $sessionId = null,
        public ?string $childName = null,
    ) {}
}
