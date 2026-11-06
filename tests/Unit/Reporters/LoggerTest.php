<?php

namespace Tests\Unit\Reporters;

use App\Reporters\Logger;
use Exception;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoggerTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new Logger;
    }

    #[Test]
    public function it_can_log_info()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->once()
            ->with('Some Message', ['data' => 'Some Data']);

        $this->logger->info('Some Message', ['data' => 'Some Data']);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_log_error()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with('Some Message', ['data' => 'Some Data']);

        $this->logger->error('Some Message', ['data' => 'Some Data']);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_log_debug()
    {
        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Log::shouldReceive('debug')
            ->once()
            ->with('Some Message', ['data' => 'Some Data']);

        $this->logger->debug('Some Message', ['data' => 'Some Data']);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_log_exception()
    {
        $exception = new Exception('Some Message');

        Log::shouldReceive('channel')
            ->once()
            ->with(null)
            ->andReturnSelf();

        Log::shouldReceive('critical')
            ->once();
        //            ->with('Some Message', [
        //                "file" => "/var/www/html/tests/Unit/Reporters/LoggerTest.php",
        //                "line" => 82,
        //                "url" => null,
        //                "parameters" => null,
        //                "verb" => "GET",
        //                "request" => [],
        //                "user" => null,
        //                "extra" => [
        //                    "data" => [
        //                        "data" => "Some Data"
        //                    ],
        //                    "debug" => $exception->getTraceAsString()
        //                ]
        //            ]);

        $this->logger->exception($exception, ['data' => 'Some Data']);

        $this->assertTrue(true);
    }
}
