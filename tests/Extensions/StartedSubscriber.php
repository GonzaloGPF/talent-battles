<?php

namespace Tests\Extensions;

use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber as StartedSubscriberInterface;

readonly class StartedSubscriber implements StartedSubscriberInterface
{
    /**
     * @throws Exception
     */
    public function __construct(private Kernel $console)
    {
        $this->checkShellCommands();

        $this->prepareDatabase();
    }

    /**
     * @throws Exception
     */
    private function checkShellCommands(): void
    {
        $commands = ['audiowaveform', 'ffmpeg', 'bpm-tag', 'soundstretch'];

        $missingCommands = collect($commands)
            ->filter(fn ($command) => ! shell_exec("which $command"));

        if ($missingCommands->isNotEmpty()) {
            throw new Exception('Missing Commands: '.$missingCommands->join(','));
        }
    }

    private function prepareDatabase(): void
    {
        collect([
            //            'config:cache',
            //            'event:cache',
            'migrate:fresh',
        ])->each(fn ($command) => $this->console->call($command));

        RefreshDatabaseState::$migrated = true;
    }

    public function notify(Started $event): void {}
}
