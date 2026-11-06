<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected bool $female = true;

    protected bool $onlyAction = true;

    private ?User $user;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth:sanctum']);
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the Notifications.
     */
    public function index(): JsonResponse
    {
        $notifications = Notification::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(NotificationResource::collection($notifications));
    }

    /**
     * Update the specified resource in storage.
     */
    public function read(?Notification $notification): JsonResponse
    {
        if ($notification->id) {
            $notification->markAsRead();

            return $this->updateResponse(new NotificationResource($notification));
        } else {
            $this->user?->unreadNotifications()->update(['read_at' => now()]);

            return $this->updateResponse(NotificationResource::collection($this->user?->unreadNotifications));
        }
    }
}
