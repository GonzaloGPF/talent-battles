<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * The XML declaration is not in the view. Blade splits templates with the PHP tokenizer, and
     * with short_open_tag on, as in production, it reads "<?xml" as PHP and stops compiling.
     */
    public function __invoke(SitemapService $sitemapService): Response
    {
        $urlset = view('sitemap', ['entries' => $sitemapService->entries()])->render();

        return response('<?xml version="1.0" encoding="UTF-8"?>'."\n".$urlset)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
