<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
use App\Scopes\ViewOnlyMine;
use App\Services\InvitationService;
use Illuminate\Http\JsonResponse;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService)
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * The invitations the user has sent, or every invitation for admins. The resource only shows the email to whoever
     * sent the invitation, and listing the others would let anyone search them by email.
     */
    public function index(): JsonResponse
    {
        $invitations = Invitation::query()
            ->withGlobalScope('mine', new ViewOnlyMine(allowAdmin: true))
            ->filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(InvitationResource::collection($invitations));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvitationRequest $request): JsonResponse
    {
        $invitations = $this->invitationService->createMultiple($request->get('invitations'));

        if ($invitations->isEmpty()) {
            return $this->emptyResponse();
        }

        return $this->storeResponse(InvitationResource::collection($invitations));
    }
}
