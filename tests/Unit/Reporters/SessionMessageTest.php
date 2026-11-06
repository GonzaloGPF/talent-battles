<?php

namespace Tests\Unit\Reporters;

use App\Reporters\SessionMessage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionMessageTest extends TestCase
{
    #[Test]
    public function it_can_flash_message_in_session()
    {
        SessionMessage::flashMessage('test');

        $this->assertEquals('test', session('message'));
    }
}
