<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class ConvertStringsToBoolean extends TransformsRequest
{
    /**
     * @param  string  $key
     * @param  mixed  $value
     * @return bool|mixed
     */
    protected function transform($key, $value)
    {
        if ($value === 'true' || $value === 'TRUE') {
            return true;
        }

        if ($value === 'false' || $value === 'FALSE') {
            return false;
        }

        return $value;
    }
}
