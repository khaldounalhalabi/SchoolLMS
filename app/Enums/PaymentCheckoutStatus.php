<?php

namespace App\Enums;

enum PaymentCheckoutStatus: string
{
    case INACTIVE_FEE = 'inactive_fee';
    case INVALID_CHILD = 'invalid_child';
    case PENDING = 'pending';
    case ALREADY_PAID = 'already_paid';
    case CREATED = 'created';
}
