<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Services\BattleShareImageService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BattleShareImageController extends Controller
{
    /**
     * The image of a battle for link previews. Its URL carries the version it shows, so that URL can be cached for
     * good; an old or missing version gets the current image, cached for an hour.
     */
    public function __invoke(Battle $battle, BattleShareImageService $shareImageService): Response
    {
        $isCurrentVersion = request('v') === $shareImageService->version($battle);

        return response((string) Storage::get($shareImageService->path($battle)), Response::HTTP_OK, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => $isCurrentVersion ? 'public, max-age=31536000, immutable' : 'public, max-age=3600',
        ]);
    }
}
