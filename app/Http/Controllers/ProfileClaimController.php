<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileClaimRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use App\Services\ProfileClaimService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileClaimController extends Controller
{
    protected bool $onlyAction = true;

    public function __construct(
        private readonly ProfileClaimService $profileClaimService,
        private readonly AuthService $authService,
    ) {
        parent::__construct();
        $this->middleware('auth')->only('link');
    }

    /**
     * The link an admin sends to the producer whose music the profile holds.
     */
    public function link(User $user): JsonResponse
    {
        $this->authorize('createClaimLink', $user);

        return $this->dataResponse(trans('help.claim_link_created'), $this->profileClaimService->createLink($user));
    }

    /**
     * The page where the producer keeps the profile or deletes it.
     */
    public function show(Request $request, User $user): View
    {
        $this->authorize('claim', [$user, $request->query('key')]);

        $user->loadCount(['tracks', 'designs']);
        $expiresAt = Carbon::createFromTimestamp((int) $request->query('expires'));

        return view('app')->with(['profileClaim' => [
            'user' => [
                'id' => $user->id,
                'nick_name' => $user->getNickName(),
                'tracks_count' => $user->tracks_count,
                'designs_count' => $user->designs_count,
            ],
            ...$this->profileClaimService->createActionLinks($user, $expiresAt),
        ]]);
    }

    public function store(ProfileClaimRequest $request, User $user): JsonResponse
    {
        $this->authorize('claim', [$user, $request->query('key')]);

        $user = $this->profileClaimService->claim($user, $request->safe()->only(['email', 'password']));

        $this->authService->login($user);

        return $this->updateResponse(new UserResource($user), 'profile_claimed');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('claim', [$user, $request->query('key')]);

        $this->profileClaimService->decline($user);

        return $this->destroyResponse(null, 'profile_deleted');
    }
}
