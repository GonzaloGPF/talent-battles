<?php

namespace App\Exceptions;

use Exception;

class TransitionNotAllowedException extends Exception
{
    public function __construct(public readonly string $from, public readonly string $to, public readonly string $model)
    {
        parent::__construct("Transition from [$from] to [$to] is not allowed on [$model].");
    }
}
