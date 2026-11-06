<?php

namespace App\Http\Controllers\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailService;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class PasswordController
 *
 * @group Auth
 */
class PasswordController extends LoginController
{
    /*
    |--------------------------------------------------------------------------
    | Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    protected bool $onlyAction = true;

    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly UserService $userService, private readonly EmailService $emailService)
    {
        parent::__construct($userService);
        $this->middleware('guest')->except('update');
        $this->middleware('auth:sanctum')->only('update');
    }

    /**
     * Email with instructions to reset User's password.
     *
     * @bodyParam email string required The email of the User that wants its password reset.
     */
    public function forgot(): JsonResponse
    {
        request()->validate(['email' => 'required|email']);

        /** @var User $user */
        $user = User::where('email', request('email'))->firstOrFail();
        $user = $this->userService->setToken($user);

        $this->emailService->createPasswordReset($user);

        return $this->updateResponse(new UserResource($user));
    }

    /**
     * Reset User's password.
     *
     * @urlParam token string required The token received by email.
     *
     * @throws AuthorizationException
     */
    public function reset(string $token): JsonResponse
    {
        request()->validate([
            'password' => 'required|confirmed|min:8|max:255',
        ]);

        $user = User::whereToken($token)->first();

        if (! $user) {
            throw new AuthorizationException(trans('validation.invalid_token'));
        }

        $user->forceFill([
            'password' => request('password'),
            'token' => null,
            'remember_token' => Str::random(60),
            'verified_at' => $user->verified_at ?? now(),
        ])->save();

        auth()->attempt([
            'email' => $user->email,
            'password' => request('password'),
        ]);

        return $this->sendResponse('logged', $user);
    }

    public function update(User $user): JsonResponse
    {
        request()
            ->merge([
                'current_password' => Hash::check(request('current_password'), $user->password),
            ])->validate([
                'current_password' => Rule::in([true]),
                'password' => 'required|confirmed|min:8|max:255',
            ]);

        $user->forceFill([
            'password' => request('password'),
        ])->save();

        return $this->updateResponse(new UserResource($user));
    }
}
