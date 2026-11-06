<?php

namespace App\Api;

use App\Facades\Logger;
use App\Interfaces\ApiInterface;
use App\Jobs\AsyncHttp;
use App\Responses\SimpleResponse;
use Exception;
use GuzzleHttp\Psr7\Stream;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class Api implements ApiInterface
{
    private Response $response;

    private PendingRequest $request;

    private array $options;

    private array $headers;

    private array $basicAuth;

    private ?string $token;

    private bool $asForm;

    private SimpleResponse $simpleResponse;

    private bool $queue;

    private bool $enabled = false;

    /**
     * HttpService constructor.
     */
    public function __construct(SimpleResponse $simpleResponse)
    {
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $this->options = [];
        $this->basicAuth = [];
        $this->token = '';
        $this->asForm = false;
        $this->simpleResponse = $simpleResponse;
        $this->queue = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function setQueue(bool $value): void
    {
        $this->queue = $value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): Api
    {
        $this->options = $options;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): Api
    {
        $this->headers = $headers;

        return $this;
    }

    public function getBasicAuth(): array
    {
        return $this->basicAuth;
    }

    public function setBasicAuth(string $username, string $password): Api
    {
        $this->basicAuth = [$username, $password];

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(?string $token): Api
    {
        $this->token = $token;

        return $this;
    }

    public function getAsForm(): bool
    {
        return $this->asForm;
    }

    public function setAsForm(): Api
    {
        $this->asForm = true;
        unset($this->headers['Content-Type']);

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function get(string $url, array $options = []): array
    {
        return $this->makeRequest('get', $url, $options);
    }

    public function put(string $url, array $data = []): array
    {
        return $this->makeRequest('put', $url, $data);
    }

    public function patch(string $url, array $data = []): array
    {
        return $this->makeRequest('patch', $url, $data);
    }

    public function post(string $url, array $data = []): array
    {
        return $this->makeRequest('post', $url, $data);
    }

    public function delete(string $url, array $data = []): array
    {
        return $this->makeRequest('delete', $url, $data);
    }

    private function makeRequest(string $verb, string $url, array $data = []): array
    {
        if ($this->queue) {
            AsyncHttp::dispatch($this->getConfig(), $verb, $url, $data);

            return [];
        }

        $response = $this->prepareRequest()
            ->doRequest($verb, $url, $data)
            ->json() ?? [];

        if ($this->response->clientError()) {
            return $this->simpleResponse->json(trans('help.whoops'), $response, $this->response->status());
        }

        if ($this->response->serverError()) {
            $message = trans('exceptions.server_error');

            Logger::error($message, $response);

            return $this->simpleResponse->json($message, $response, $this->response->status());
        }

        return $response;
    }

    public function prepareRequest(): Api
    {
        $this->request = Http::withOptions($this->options)
            ->withHeaders($this->headers);

        if (! empty($this->basicAuth)) {
            $this->request->withBasicAuth($this->basicAuth[0], $this->basicAuth[1]);
        }

        if (! empty($this->token)) {
            $this->request->withToken($this->token);
        }

        if ($this->asForm) {
            $this->request->asForm();
        }

        return $this;
    }

    public function doRequest(string $verb, string $url, array $data): Response
    {
        $this->response = $this->canMakeRequest()
            ? $this->request->$verb($url, $data)
            : $this->fakeResponse();

        $this->doLog($verb, $url, $data, optional($this->response)->json());

        return $this->response;
    }

    /**
     * It will log correctly depending on environments
     * If its on live, it will throws exceptions logs
     * if its local or testing, it will just log in file
     */
    private function doLog(string $verb, string $url, array $data = [], array|Response|null $response = null)
    {
        $config = $this->getConfig();

        $data = array_merge([
            'meta' => compact('config', 'verb', 'url', 'response'),
            'request' => $data,
        ]);

        try {
            Logger::http($url, $data, $verb);
        } catch (Exception $exception) {
            Logger::exception($exception, $data);
        }
    }

    public function getConfig(): array
    {
        return [
            'auth' => $this->basicAuth,
            'options' => $this->options,
            'token' => $this->token,
            'asForm' => $this->asForm,
            'headers' => $this->getHeaders(),
        ];
    }

    private function canMakeRequest(): bool
    {
        if ($this->enabled) {
            return true;
        }

        return app()->environment(['testing', 'production']);
    }

    private function fakeResponse(): Response
    {
        $body = new Stream(fopen(base_path('tests/mocks/ok.json'), 'r'));

        return new Response(new \GuzzleHttp\Psr7\Response(200, [], $body));
    }
}
