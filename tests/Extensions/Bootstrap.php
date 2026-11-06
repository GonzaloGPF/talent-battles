<?php

namespace Tests\Extensions;

use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Tests\CreatesApplication;

/**
 * Class Bootstrap. A PhpUnit custom extension
 */
final class Bootstrap implements Extension
{
    /*
    |--------------------------------------------------------------------------
    | Bootstrap The Test Environment
    |--------------------------------------------------------------------------
    |
    | You may specify console commands that execute once before your test is
    | run. You are free to add your own additional commands or logic into
    | this file as needed in order to help your test suite run quicker.
    |
    */

    use CreatesApplication;

    private Kernel $console;

    public function __construct()
    {
        $app = $this->createApplication();

        $this->console = $app->make(Kernel::class);
    }

    /**
     * @throws Exception
     */
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        new StartedSubscriber($this->console);

        //        $facade->registerSubscriber(new StartedSubscriber($this->console));
        $facade->registerSubscriber(new ExecutionFinishedSubscriber($this->console));

        // This application only prepares the database: its error handlers must not outlive it,
        // or PHPUnit reports every test as risky when the test's own application flushes them.
        HandleExceptions::flushState();
    }
}
