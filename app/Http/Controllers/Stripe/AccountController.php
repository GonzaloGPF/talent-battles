<?php

namespace App\Http\Controllers\Stripe;

use App\Api\StripeApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    protected bool $onlyAction = true;

    public function __construct(private readonly StripeApi $stripeApi)
    {
        parent::__construct();
        $this->middleware('auth:sanctum');
        $this->middleware('is-admin')->only('index');
    }

    public function index(): JsonResponse
    {
        $accounts = $this->stripeApi->getAccounts();

        return $this->indexResponse(JsonResource::collection($accounts));
    }

    /**
     * @throws ValidationException
     */
    public function store(UserService $userService): JsonResponse
    {
        $user = $userService->setAccount();

        if (empty($user->account)) {
            return $this->emptyResponse();
        }

        return $this->showResponse(new UserResource($user));
    }

    public function destroy(): JsonResponse
    {
        $this->stripeApi->destroyAccount();

        auth()->user()
            ->forceFill(['account' => null])
            ->save();

        return $this->destroyResponse();
    }

    public function link(): JsonResponse
    {
        $accountLink = $this->stripeApi->createAccountLink();

        return $this->showResponse(JsonResource::make(['link' => $accountLink]));
    }
}
