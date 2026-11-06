<?php

namespace App\Http\Controllers;

use App\Responses\SimpleResponse;
use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class WelcomeController extends Controller
{
    public function __construct(private SimpleResponse $simpleResponse, private UserService $userService)
    {
        parent::__construct();
    }

    public function index(): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return $this->simpleResponse->jsonData(trans('exceptions.not_found'), [
                'path' => request()->url(),
            ], 404);
        }

        return view('app')->with(['user' => $this->userService->getUserData()]);
    }
}
