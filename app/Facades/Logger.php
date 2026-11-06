<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Logger extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \App\Reporters\Logger::class;
    }
}
