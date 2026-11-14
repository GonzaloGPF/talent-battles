<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileClaimRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Responses\SimpleResponse;
use App\Services\ProfileClaimService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ProfileClaimController extends Controller
{
    public function __construct(private readonly ProfileClaimService $profileClaimService)
    {
        parent::__construct();
        $this->middleware('auth')->only('link');
    }

    /**
     * The link an admin sends to the producer whose music the profile holds.
     */
    public function link(User $user): JsonResponse
    {
        $this->authorize('claim', $user);

        return $this->dataResponse(trans('help.claim_link_created'), $this->profileClaimService->createLink($user));
    }

    /**
     * The page where the producer keeps the profile or deletes it.
     */
    public function show(Request $request, User $user): View
    {
        abort_unless($this->profileClaimService->isValidKey($user, $request->query('key')), Response::HTTP_FORBIDDEN);

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
        $user = $this->profileClaimService->claim($user, $request->safe()->only(['email', 'password']));

        auth('web')->login($user);
        $request->session()->regenerate();

        return resolve(SimpleResponse::class)->resource(trans('help.profile_claimed'), new UserResource($user), Response::HTTP_ACCEPTED);
    }

    public function destroy(ProfileClaimRequest $request, User $user): JsonResponse
    {
        $this->profileClaimService->decline($user);

        return $this->dataResponse(trans('help.profile_deleted'), null, Response::HTTP_ACCEPTED);
    }
}
