<?php

namespace App\Enums\Payment;

enum PaymentMethod: string
{
    case Online = 'online';
    case Cash = 'cash';
}
