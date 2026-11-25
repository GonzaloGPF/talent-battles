<?php

namespace App\Responses;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class SimpleResponse
{
    /**
     * Builds up app's json response structure
     *
     * @param  array<mixed>|null  $data
     * @return array{code: int, message: string, data: array<mixed>|null}
     */
    public function json(string $message, ?array $data = null, int $code = Response::HTTP_OK): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Returns a json response that can includes specific data.
     *
     * @param  array<mixed>|null  $data
     */
    public function jsonData(string $message, ?array $data = null, int $code = Response::HTTP_OK): JsonResponse
    {
        $jsonData = $this->json($message, $data, $code);

        return response()->json($jsonData, $code);
    }

    /**
     * Builds up a json response using an Api Resource.
     */
    public function resource(string $message, JsonResource $resource, int $code): JsonResponse
    {
        return $resource
            ->additional(array_merge(compact('code', 'message'), $resource->additional))
            ->response()
            ->setStatusCode($code);
    }

    public function empty(): JsonResponse
    {
        return $this->jsonData(trans('help.no_content'))
            ->setStatusCode(Response::HTTP_NO_CONTENT, trans('help.no_content'));
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<mixed>  $debug
     */
    public function exception(Exception|string|null $exception, int $code = Response::HTTP_FORBIDDEN, ?array $data = [], array $debug = []): JsonResponse
    {
        if ($exception instanceof Exception) {
            $message = $data['message'] ?? $exception->getMessage();
            if ($message === 'This action is unauthorized.') {
                $message = trans('exceptions.unauthorized');
            }
        } elseif (Lang::has("exceptions.$exception")) {
            $message = trans("exceptions.$exception");
        } else {
            $message = $exception ?? trans('exceptions.server_error');
        }

        if (! empty($debug)) {
            Log::debug($message, $debug);
        }

        return $this->jsonData($message, $data, $code);
    }
}
