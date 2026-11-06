<?php

namespace App\Reporters;

use App\Enums\LogLevels;
use App\Models\Http;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class Logger
{
    public function info(string $message, ?array $data = [], ?string $channel = null): void
    {
        $this->log($message, $data, LogLevels::INFO, $channel);
    }

    public function error(string $message, ?array $data = null, ?string $channel = null): void
    {
        $this->log($message, $data, LogLevels::ERROR, $channel);
    }

    public function debug(string $message, ?array $data = [], ?string $channel = null): void
    {
        $this->log($message, $data, LogLevels::DEBUG, $channel);
    }

    public function exception(Throwable $exception, ?array $data = null, ?string $channel = null): void
    {
        $this->log($exception->getMessage(), $this->getErrorData($exception, $data), LogLevels::CRITICAL, $channel);
    }

    public function http(string $url, ?array $data, ?string $verb = 'GET', ?bool $outgoing = true, ?string $description = null): void
    {
        $data = [
            'url' => $url,
            'data' => $data,
            'verb' => Str::upper($verb),
            'outgoing' => $outgoing,
            'description' => $description,
        ];

        try {
            Http::forceCreate($data);
        } catch (Exception $exception) {
            $this->exception($exception, $data);
        }
    }

    private function getErrorData(Throwable $exception, ?array $data = []): array
    {
        /** @var User $user */
        $user = auth()->user();
        $exceptionData = [];

        if (method_exists($exception, 'getDebugData')) {
            $exceptionData = $exception->getDebugData();
        }

        if (! $exceptionData) {
            $exceptionData = $exception->getTraceAsString();
        }

        return [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => request()->route()?->uri,
            'parameters' => request()->route()?->parameters(),
            'verb' => request()->method(),
            'request' => request()->all(),
            'user' => $user?->only(['id', 'name', 'email']),
            'data' => $data,
            'debug' => $exceptionData,
        ];
    }

    /**
     * @param  null  $channel
     */
    private function log(string $message, array $data = [], ?LogLevels $level = null, $channel = null): void
    {
        $logger = Log::channel($channel);

        try {
            match ($level) {
                LogLevels::NOTICE => $logger->notice($message, $data),
                LogLevels::WARNING => $logger->warning($message, $data),
                LogLevels::ERROR => $logger->error($message, $data),
                LogLevels::DEBUG => $logger->debug($message, $data),
                LogLevels::CRITICAL => $logger->critical($message, $data),
                LogLevels::ALERT => $logger->alert($message, $data),
                LogLevels::EMERGENCY => $logger->emergency($message, $data),
                default => $logger->info($message, $data),
            };
        } catch (Exception $exception) {
            logger('Logger Exception', ['message' => $exception->getMessage()]);
        }
    }
}
