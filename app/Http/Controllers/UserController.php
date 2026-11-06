<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EmailService;
use App\Services\FileService;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * Class UserController
 * Users management
 *
 * @group Users
 */
class UserController extends Controller
{
    /**
     * Display a listing of the User.
     *
     * @apiResourceCollection App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function index(): JsonResponse
    {
        $users = User::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(UserResource::collection($users));
    }

    /**
     * Display the specified User.
     *
     * @apiResource App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function show(User $user): JsonResponse
    {
        return $this->showResponse(new UserResource($user));
    }

    /**
     * Store a newly created User in storage.
     *
     * @apiResource App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     */
    public function store(UserRequest $request, UserService $userService, FileService $fileService, EmailService $emailService): JsonResponse
    {
        $user = $userService->create($request->validated());

        if (! $request->get('password')) {
            $emailService->createVerifyEmail($user);
        }

        if ($image = $this->getFile('image')) {
            $fileService->saveFile($user, $image);
        }

        return $this->storeResponse(new UserResource($user));
    }

    /**
     * Update the specified User in storage.
     *
     * @apiResource App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     *
     * @throws AuthorizationException
     */
    public function update(UserRequest $request, User $user, UserService $userService, FileService $fileService): JsonResponse
    {
        $this->authorize('update', $user);

        $user = $userService->update($user, $request->validated());

        if ($image = $this->getFile('image')) {
            if ($user->image) {
                $fileService->updateFile($user->image, $image);
            } else {
                $fileService->saveFile($user, $image);
            }
        }

        return $this->updateResponse(new UserResource($user));
    }

    /**
     * Remove the specified User from storage.
     *
     * @apiResource App\Http\Resources\UserResource
     *
     * @apiResourceModel App\Models\User
     *
     * @throws AuthorizationException
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $this->destroyModel($user);

        return $this->destroyResponse(new UserResource($user));
    }
}
