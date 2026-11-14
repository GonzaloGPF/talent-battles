<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * Class RegisterController
 *
 * @group Users
 */
class RegisterController extends Controller
{
    protected string $modelName = 'user';

    /**
     * Store a newly created User in storage.
     *
     * @apiResource App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function register(UserService $userService, AuthService $authService, RegisterRequest $request): JsonResponse
    {
        $user = $userService->create($request->validated());

        UserRegistered::dispatch($user);

        $authService->login($user);

        return $this->storeResponse(new UserResource($user));
    }
}
