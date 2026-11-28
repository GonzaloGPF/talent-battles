<?php

use App\Exceptions\ExceptionRenderer;
use App\Facades\Logger;
use App\Http\Middleware\AddContentSecurityPolicyHeaders;
use App\Http\Middleware\AddFrontendVersionHeader;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ConvertStringsToBoolean;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\Language;
use App\Http\Middleware\PreventRequestsDuringMaintenance;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustHosts;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\ValidateSignature;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    // EventServiceProvider lists every listener: discovering them as well would run each one twice.
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware): void {
        // Language runs twice: here for errors raised before routing, and in the groups once the session has started.
        $middleware->use([
            TrustHosts::class,
            TrustProxies::class,
            HandleCors::class,
            Language::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            ConvertStringsToBoolean::class,
        ]);

        $middleware->group('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            Language::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,
            Language::class,
            ThrottleRequests::class.':api',
            SubstituteBindings::class,
            AddFrontendVersionHeader::class,
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'is-admin' => IsAdmin::class,
            'csp' => AddContentSecurityPolicyHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([
            ValidationException::class,
            AuthenticationException::class,
            ModelNotFoundException::class,
            NotFoundHttpException::class,
            FileNotFoundException::class,
            RelationNotFoundException::class,
            AuthorizationException::class,
            UnauthorizedHttpException::class,
            CommandNotFoundException::class,
        ]);

        $exceptions->report(function (Throwable $e): void {
            try {
                Logger::exception($e);
            } catch (Throwable $loggerException) {
                info('The logger could not report an exception: '.$loggerException->getTraceAsString());
            }
        });

        $exceptions->render(fn (Throwable $e, Request $request) => resolve(ExceptionRenderer::class)->render($e, $request));
    })
    ->create();
