<?php

namespace App\Http\Middleware;

class ValidateSignature extends \Illuminate\Routing\Middleware\ValidateSignature
{
    protected $ignore = [
        'payment_intent',
        'payment_intent_client_secret',
        'redirect_status',
    ];
}
