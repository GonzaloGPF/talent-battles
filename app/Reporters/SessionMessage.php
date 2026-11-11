<?php

namespace App\Reporters;

class SessionMessage
{
    /**
     * Keeps a message for the next page of the SPA, which pulls it from the session when it renders.
     * It is not flashed: the session starts twice on web requests (global middleware and web group),
     * so flashed data would be gone before a redirect reaches the next page.
     */
    public static function flashMessage(string $message, ?string $type = null): void
    {
        session()->put('message', ['message' => $message, 'type' => $type]);
    }
}
