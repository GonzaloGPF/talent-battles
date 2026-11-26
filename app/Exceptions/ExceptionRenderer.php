<?php

namespace App\Exceptions;

use App\Responses\SimpleResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Exceptions\InvalidCustomer;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Answers the SPA's JSON requests with the app's own error format, and handles the two cases of pages that differ
 * from Laravel's. Anything else falls back to Laravel's rendering.
 */
class ExceptionRenderer
{
    public function __construct(private readonly SimpleResponse $simpleResponse) {}

    public function render(Throwable $e, Request $request): ?SymfonyResponse
    {
        $e = $this->original($e);

        if ($request->expectsJson()) {
            return $this->json($e, $request);
        }

        if ($e instanceof FileNotFoundException) {
            return response(trans('exceptions.not_found'), Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->guest(route('welcome'));
        }

        return null;
    }

    private function json(Throwable $e, Request $request): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->simpleResponse->exception('validation', Response::HTTP_UNPROCESSABLE_ENTITY, $e->validator->getMessageBag()->toArray());
        }

        if ($e instanceof ModelNotFoundException) {
            $model = Str::lower(class_basename($e->getModel()));

            return $this->simpleResponse->exception(new Exception(trans('exceptions.not_found_data', ['data' => modelTitle($model)])), Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof NotFoundHttpException || $e instanceof FileNotFoundException) {
            return $this->simpleResponse->exception($e->getMessage() ?: 'not_found', Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof RelationNotFoundException) {
            preg_match('/\[(.+?)\]/', $e->getMessage(), $match);

            return $this->simpleResponse->exception(new Exception(trans('exceptions.no_relation', ['data' => $match[1] ?? ''])), Response::HTTP_NOT_FOUND);
        }

        if ($e instanceof AuthorizationException) {
            return $this->simpleResponse->exception($e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->simpleResponse->exception('authentication', Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof FilterException) {
            return $this->simpleResponse->exception($e, Response::HTTP_BAD_REQUEST, $e->getErrors(), $e->getDebugData());
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->simpleResponse
                ->exception(new Exception(trans('exceptions.wrong_verb', ['verb' => $request->getMethod()])), Response::HTTP_METHOD_NOT_ALLOWED)
                ->withHeaders($e->getHeaders());
        }

        if ($e instanceof TokenMismatchException) {
            return $this->simpleResponse->exception($e, Response::HTTP_UNAUTHORIZED, ['message' => trans('exceptions.session_expired'), 'action' => 'reload']);
        }

        if ($e instanceof InvalidCustomer) {
            return $this->simpleResponse->exception($e, Response::HTTP_BAD_REQUEST, ['message' => trans('exceptions.invalid_customer')]);
        }

        if ($e instanceof InvalidSignatureException) {
            return $this->simpleResponse->exception($e, Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof ThrottleRequestsException) {
            $message = trans('exceptions.too_many_attempts', ['seconds' => $e->getHeaders()['Retry-After'] ?? 60]);

            return $this->simpleResponse
                ->exception(new Exception($message), Response::HTTP_TOO_MANY_REQUESTS)
                ->withHeaders($e->getHeaders());
        }

        if ($e instanceof HttpException) {
            return $this->simpleResponse
                ->exception($e->getMessage() !== '' ? $e : $this->httpErrorMessage($e->getStatusCode()), $e->getStatusCode())
                ->withHeaders($e->getHeaders());
        }

        return $this->simpleResponse->exception('server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Laravel turns some exceptions into HTTP ones before render callbacks see them. The app answers from the original
     * one: its own message for a missing model, and the reload action for an expired CSRF token.
     */
    private function original(Throwable $e): Throwable
    {
        $previous = $e->getPrevious();

        return $e instanceof HttpException && (
            $previous instanceof ModelNotFoundException
            || $previous instanceof AuthorizationException
            || $previous instanceof TokenMismatchException
        ) ? $previous : $e;
    }

    /**
     * The exceptions translation for an HTTP error that came without a message.
     */
    private function httpErrorMessage(int $statusCode): string
    {
        return match (true) {
            $statusCode === Response::HTTP_UNAUTHORIZED => 'authentication',
            $statusCode === Response::HTTP_FORBIDDEN => 'unauthorized',
            $statusCode === Response::HTTP_NOT_FOUND => 'not_found',
            $statusCode === Response::HTTP_REQUEST_ENTITY_TOO_LARGE => 'too_large',
            $statusCode >= Response::HTTP_INTERNAL_SERVER_ERROR => 'server_error',
            default => 'request_failed',
        };
    }
}
