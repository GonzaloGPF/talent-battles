<?php

namespace App\Interfaces;

use App\Api\Api;
use Illuminate\Http\Client\Response;

interface ApiInterface
{
    public function prepareRequest(): Api;

    /**
     * @param  array<string, mixed>  $options
     */
    public function setOptions(array $options): Api;

    /**
     * @param  array<string, mixed>  $data
     */
    public function doRequest(string $verb, string $url, array $data): Response;

    /**
     * @param  array<string, mixed>  $options
     * @return array<mixed>
     */
    public function get(string $url, array $options = []): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function put(string $url, array $data = []): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function patch(string $url, array $data = []): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function post(string $url, array $data = []): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<mixed>
     */
    public function delete(string $url, array $data = []): array;
}
