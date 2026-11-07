<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Design;
use App\Models\Track;
use App\Models\User;
use App\Responses\PageResponse;
use App\Services\PageMetaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Pages of the SPA. They all render the same view, each one with its own meta tags.
 */
class PageController extends Controller
{
    public const SECTIONS = ['beats', 'songs', 'samples', 'designs', 'battles', 'users', 'contributors'];

    public const PRIVATE_SECTIONS = ['admin', 'carts', 'commerce', 'password', 'profile', 'stats'];

    public function __construct(private PageResponse $pageResponse, private PageMetaService $pageMetaService)
    {
        parent::__construct();
    }

    public function index(string $section): Response
    {
        return $this->pageResponse->render($this->pageMetaService->section($section));
    }

    /**
     * A track opened under the URL of another type, like a song in /beats/1, goes to its own URL.
     */
    public function track(string $trackType, Track $track): Response|RedirectResponse
    {
        if ($trackType !== $track->track_type->getUrlSegment()) {
            return redirect()->to($this->pageMetaService->trackUrl($track), Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->pageResponse->render($this->pageMetaService->track($track));
    }

    public function design(Design $design): Response
    {
        return $this->pageResponse->render($this->pageMetaService->design($design));
    }

    public function album(Album $album): Response
    {
        return $this->pageResponse->render($this->pageMetaService->album($album));
    }

    public function user(User $user): Response
    {
        return $this->pageResponse->render($this->pageMetaService->user($user));
    }

    public function private(): Response
    {
        return $this->pageResponse->render();
    }
}
