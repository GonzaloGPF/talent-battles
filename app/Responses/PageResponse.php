<?php

namespace App\Responses;

use App\Objects\PageMeta;
use App\Services\UserService;
use Illuminate\Http\Response;

class PageResponse
{
    public function __construct(private UserService $userService) {}

    /**
     * Renders the SPA. A page without meta tags is not indexed by search engines.
     */
    public function render(?PageMeta $meta = null, int $status = Response::HTTP_OK): Response
    {
        return response()->view('app', [
            'user' => $this->userService->getUserData(),
            'meta' => $meta,
        ], $status);
    }
}
