<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;

/**
 * @mixin Comment
 */
class CommentResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model' => $this->addModel(),
            'created_by' => $this->created_by,
            'comment_id' => $this->comment_id,
            'content' => $this->content,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'files' => FileResource::collection($this->whenLoaded('files')),
            'like' => new LikeResource($this->whenLoaded('like')),
            $this->addCount('likes'),
        ];
    }
}
