<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * In production the public nginx passes every request to another nginx on the same server, so PHP sees them all
     * coming from the loopback address.
     *
     * @var array<int, string>
     */
    protected $proxies = ['127.0.0.1', '::1'];

    /**
     * Only the visitor's IP is taken from the proxy: nginx appends it to X-Forwarded-For, so a visitor cannot forge it.
     * Other forwarded headers would pass through from the visitor unchanged.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR;
}
