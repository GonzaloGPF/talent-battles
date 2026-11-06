<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EmailService $emailService
    ) {
        parent::__construct();
    }

    public function redirect(string $driver): RedirectResponse
    {
        return Socialite::driver($driver)->redirect();
    }

    public function callback(string $driver): View // TODO: test it
    {
        $oAuthUser = Socialite::driver($driver)->user();

        $user = $this->userService->createFromOAuth($driver, $oAuthUser);

        auth('web')->loginUsingId($user->id);

        $this->emailService->createPasswordReset($user, true);

        // TODO: send Notification to front

        return view('app')->with(['user' => $this->userService->getUserData()]);
    }
}
