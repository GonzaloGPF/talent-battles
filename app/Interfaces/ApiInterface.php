<?php

namespace App\Interfaces;

use App\Api\Api;
use Illuminate\Http\Client\Response;

interface ApiInterface
{
    public function prepareRequest(): Api;

    public function setOptions(array $options): Api;

    public function doRequest(string $verb, string $url, array $data): Response;

    public function get(string $url, array $options = []): array;

    public function put(string $url, array $data = []): array;

    public function patch(string $url, array $data = []): array;

    public function post(string $url, array $data = []): array;

    public function delete(string $url, array $data = []): array;
}
