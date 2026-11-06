<?php

namespace App\Jobs;

use App\Interfaces\ApiInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AsyncHttp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $options;

    private string $verb;

    private string $url;

    private array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $options, string $verb, string $url, array $data = [])
    {
        $this->options = $options;
        $this->verb = $verb;
        $this->url = $url;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(ApiInterface $api): void
    {
        $api->setOptions($this->options)
            ->prepareRequest()
            ->doRequest($this->verb, $this->url, $this->data);
    }
}
