<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

/**
 * Class CommentController
 *
 * @group Comments
 */
class CommentController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->except('index');
    }

    /**
     * Display a listing of the Comment.
     */
    public function index(): JsonResponse
    {
        $comments = Comment::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(CommentResource::collection($comments));
    }

    /**
     * Store a newly created Comment in storage.
     */
    public function store(CommentRequest $request): JsonResponse
    {
        /** @var Comment $comment */
        $comment = (new Comment($request->validated()))
            ->commentable()
            ->associate(morphedModel($request->get('model_type'), $request->get('model_id')));

        $comment->save();

        $this->saveFilesTo($comment);

        $comment->load(['createdBy.image', 'files']);

        if (! $comment->comment_id) { // TODO: test it
            CommentCreated::dispatch($comment);
        }

        return $this->storeResponse(new CommentResource($comment));
    }

    /**
     * Remove the specified Comment from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $this->destroyModel($comment);

        return $this->destroyResponse();
    }
}
