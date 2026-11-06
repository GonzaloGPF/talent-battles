<?php

namespace Tests\Extensions;

use Illuminate\Contracts\Console\Kernel;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber as ExecutionFinishedSubscriberInterface;

readonly class ExecutionFinishedSubscriber implements ExecutionFinishedSubscriberInterface
{
    public function __construct(private Kernel $console)
    {
        array_map('unlink', glob('bootstrap/cache/*.phpunit.php'));

        collect([
            'config:clear',
            'event:clear',
        ])->each(fn ($command) => $this->console->call($command));
    }

    public function notify(ExecutionFinished $event): void {}
}
