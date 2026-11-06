<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;

class TagController extends Controller
{
    /**
     * Display a listing of the Tag.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(TagResource::collection($tags));
    }
}
