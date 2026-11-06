<?php

namespace App\Http\Resources;

use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Download
 */
class DownloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
