<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvitationRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Invitation;
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
     * Display a listing of the Invitation.
     */
    public function index(): JsonResponse
    {
        $invitations = Invitation::filter(request()->all())
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
