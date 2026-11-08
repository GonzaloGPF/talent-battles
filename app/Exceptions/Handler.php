<?php

namespace App\Exceptions;

use App\Facades\Logger;
use App\Responses\SimpleResponse;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Exceptions\InvalidCustomer;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    private SimpleResponse $simpleResponse;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->simpleResponse = resolve(SimpleResponse::class);
    }

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        ModelNotFoundException::class,
        NotFoundHttpException::class,
        FileNotFoundException::class,
        RelationNotFoundException::class,
        AuthorizationException::class,
        UnauthorizedHttpException::class,
        CommandNotFoundException::class,
        //        FilterException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send to Sentry, Bugsnag, etc.
     *
     *
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        if (! in_array(get_class($e), $this->dontReport)) {
            try {
                Logger::exception($e);
            } catch (Throwable $e) {
                info(get_class($this).' '.$e->getTraceAsString());
            }
        }
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return $this->simpleResponse->exception('validation', Response::HTTP_UNPROCESSABLE_ENTITY, $e->validator->getMessageBag()->toArray());
            }

            if ($e instanceof ModelNotFoundException) {
                preg_match('/\[(.+?)]/', $e->getMessage(), $match);
                $model = Str::lower(str_replace('App\\Models\\', '', $match[1]));

                return $this->simpleResponse->exception(new Exception(trans('exceptions.not_found_data', ['data' => modelTitle($model)])), Response::HTTP_NOT_FOUND);
            }

            if ($e instanceof NotFoundHttpException || $e instanceof FileNotFoundException) {
                return $this->simpleResponse->exception($e->getMessage() ?? 'not_found', Response::HTTP_NOT_FOUND);
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
                return $this->simpleResponse->exception(new Exception(trans('exceptions.wrong_verb', ['verb' => $request->getMethod()])));
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

            return $this->simpleResponse->exception('server_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($e instanceof FileNotFoundException) {
            return response(trans('exceptions.not_found'), Response::HTTP_NOT_FOUND);
        }

        return parent::render($request, $e);
    }

    /**
     * @param  Request  $request
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return $this->simpleResponse->exception('authentication', Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->guest(route('welcome'));
    }
}
