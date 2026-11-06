<?php

namespace App\Http\Resources;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin File
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'created_by' => $this->created_by,
            'fileable_id' => $this->fileable_id,
            'model_type' => str_replace('App\\Models\\', '', $this->fileable_type),
            'file_type' => $this->file_type,
            'name' => $this->name,
            'extension' => $this->getExtension(),
            'label' => $this->fullName(),
            'mime' => $this->mime,
            //            'generated' => $this->generated,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
        ];
    }
}
