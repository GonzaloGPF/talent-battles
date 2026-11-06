<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;

class BillingPortalController extends Controller
{
    public function __invoke(UserService $userService): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $userService->getRedirectToBillingPortal($user);
    }
}
