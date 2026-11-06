<?php

namespace App\Enums;

enum PaymentStates: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case ERROR = 'error';
}
