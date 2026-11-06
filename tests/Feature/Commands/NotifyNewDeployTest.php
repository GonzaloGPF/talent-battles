<?php

namespace Tests\Feature\Commands;

use App\Events\NewDeploy;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotifyNewDeployTest extends TestCase
{
    #[Test]
    public function it_dispatches_new_vue_deploy_event()
    {
        Event::fake();

        Artisan::call('notify:new_deploy');

        Event::assertDispatched(NewDeploy::class);
    }
}
