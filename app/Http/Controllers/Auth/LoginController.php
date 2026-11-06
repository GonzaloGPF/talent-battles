<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Class LoginController
 *
 * @group Auth
 */
class LoginController extends Controller
{
    protected bool $onlyAction = true;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    /**
     * Login into the application.
     * After a successful Login, you will receive a token in response.
     * Use the given token in every request as an Authorization header
     *
     * @bodyParam email string required The email of the User.
     * @bodyParam password string required The password of the User.
     *
     * @response {
     *  "code": 202,
     *  "message": "Login",
     *  "data": {
     *    "token": "<token>",
     *    "token_type": "Bearer",
     *    "user": "<userData>"
     *  }
     * }
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        if (auth()->check()) {
            $this->sendResponse('logged', auth()->user(), Response::HTTP_ACCEPTED);
        }

        $request->validate([
            'email' => 'string|required|email|exists:users,email',
            'password' => 'string|required',
        ]);

        if (! auth()->attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        return $this->sendResponse('logged', auth()->user(), Response::HTTP_ACCEPTED);
    }

    /**
     * Impersonate User. Only Admins.
     *
     * @throws AuthorizationException
     */
    public function impersonate(User $user): JsonResponse
    {
        if (! hasRole(Roles::ADMIN)) {
            throw new AuthorizationException(trans('exceptions.unauthorized'));
        }

        auth('web')->loginUsingId($user->id); // Sanctum will use 'web' guard if can

        return $this->sendResponse('impersonated', $this->userService->getUserData($user), Response::HTTP_ACCEPTED);
    }

    public function me(): JsonResponse
    {
        return $this->sendResponse('show', $this->userService->getUserData());
    }

    /**
     * Log out of the application.
     */
    public function logout(Request $request): JsonResponse
    {
        User::flushEventListeners();

        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $user->tokens()
                ->where('id', auth()->id())
                ->delete();
            $user->token = null;
        }

        auth()->logout();

        //        session()->invalidate();

        return $this->sendResponse('logout', $user ?? null, Response::HTTP_ACCEPTED);
    }

    protected function sendResponse(string $verb, ?User $user, int $code = Response::HTTP_OK): JsonResponse
    {
        if (! $user) {
            return $this->dataResponse($this->buildMessage($verb), null, $code);
        }

        $user = $this->userService->loadRelations($user);

        $data = $this->isFromFrontend()
            ? (new UserResource($user))->resolve()
            : $this->userService->getJWTData($user);

        return $this->dataResponse($this->buildMessage($verb), $data, $code);
    }

    private function isFromFrontend(): bool
    {
        return request()->hasHeader('X-Requested-With'); // TODO: Try to improve front-end detection
    }
}
