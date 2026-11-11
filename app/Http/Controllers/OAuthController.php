<?php

namespace App\Http\Controllers;

use App\Reporters\SessionMessage;
use App\Services\EmailService;
use App\Services\UserService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthController extends Controller
{
    /**
     * Providers users can sign in with. Any other one would reach Socialite and fail with an error 500.
     */
    public const DRIVERS = ['google', 'facebook'];

    public function __construct(
        private readonly UserService $userService,
        private readonly EmailService $emailService
    ) {
        parent::__construct();
    }

    /**
     * Sends the visitor to the provider. The optional "redirect" query is the page of this site to come back to.
     */
    public function redirect(Request $request, string $driver): SymfonyRedirectResponse
    {
        $path = $request->query('redirect');

        if (is_string($path) && $this->isLocalPath($path)) {
            redirect()->setIntendedUrl(url($path));
        } else {
            $request->session()->forget('url.intended');
        }

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Signs the visitor in and takes them back to the page they came from.
     * Only new accounts get the email to set a password.
     */
    public function callback(Request $request, string $driver): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->failedLogin();
        }

        try {
            $oAuthUser = Socialite::driver($driver)->user();
        } catch (InvalidStateException|RequestException) {
            return $this->failedLogin();
        }

        if (! $oAuthUser->getEmail()) {
            return $this->failedLogin();
        }

        $user = $this->userService->createFromOAuth($driver, $oAuthUser);

        auth('web')->login($user);
        $request->session()->regenerate();

        if ($user->wasRecentlyCreated) {
            $this->emailService->createPasswordReset($user, true);
        }

        return redirect()->intended('/');
    }

    private function failedLogin(): RedirectResponse
    {
        SessionMessage::flashMessage(trans('help.oauth_failed'), 'error');

        return redirect()->intended('/');
    }

    /**
     * A path of this site. "//host" or "https://host" would send the visitor to another site.
     */
    private function isLocalPath(string $path): bool
    {
        return Str::startsWith($path, '/') && ! Str::startsWith($path, ['//', '/\\']);
    }
}
