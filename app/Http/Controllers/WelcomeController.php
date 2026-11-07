<?php

namespace App\Http\Controllers;

use App\Responses\PageResponse;
use App\Responses\SimpleResponse;
use App\Services\PageMetaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WelcomeController extends Controller
{
    public function __construct(
        private SimpleResponse $simpleResponse,
        private PageResponse $pageResponse,
        private PageMetaService $pageMetaService,
    ) {
        parent::__construct();
    }

    public function index(): Response
    {
        return $this->pageResponse->render($this->pageMetaService->home());
    }

    /**
     * Any path without a route. The SPA still answers it, but with a 404 so that search engines
     * do not index it as a copy of the home page.
     */
    public function notFound(): Response|JsonResponse
    {
        if (request()->wantsJson()) {
            return $this->simpleResponse->jsonData(trans('exceptions.not_found'), [
                'path' => request()->url(),
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->pageResponse->render(status: Response::HTTP_NOT_FOUND);
    }
}
