<?php

namespace App\Enums\Payment;

enum PaymentStatus :string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case  Refunded = 'refunded';
}
