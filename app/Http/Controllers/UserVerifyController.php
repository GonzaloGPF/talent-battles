<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Reporters\SessionMessage;
use App\Services\UserService;
use Illuminate\Contracts\View\View;

class UserVerifyController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    public function update(User $user): View
    {
        $user->forceFill([
            'token' => null,
            'verified_at' => now(),
        ])->save();

        SessionMessage::flashMessage(tAction('verified', null, false));

        return view('app')->with(['user' => $this->userService->getUserData()]);
    }
}
