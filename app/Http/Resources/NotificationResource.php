<?php

namespace App\Http\Resources;

use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * @mixin Notification
 */
class NotificationResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notifiable_id' => $this->notifiable_id,
            'notifiable_type' => $this->notifiable_type,
            'model' => $this->addModel(),
            'type' => class_basename($this->type),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'notifiable' => $this->morphedResource('notifiable'),
            $this->merge($this->data),
        ];
    }
}
