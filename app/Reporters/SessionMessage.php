<?php

namespace App\Reporters;

class SessionMessage
{
    /**
     * Keeps a message for the next page of the SPA, which shows it when it renders.
     */
    public static function flashMessage(string $message, ?string $type = null): void
    {
        session()->flash('message', ['message' => $message, 'type' => $type]);
    }
}
