<?php

namespace App\Reporters;

class SessionMessage
{
    public static function flashMessage(string $message): void
    {
        session()->flash('message', $message);
    }
}
