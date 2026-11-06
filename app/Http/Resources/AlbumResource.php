<?php

namespace App\Http\Resources;

use App\Models\Album;
use Illuminate\Http\Request;

/**
 * @mixin Album
 */
class AlbumResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'model' => $this->addModel(),
            'description' => $this->description,
            'released' => $this->released?->toDateString(),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'image' => new FileResource($this->whenLoaded('image')),
            'like' => new LikeResource($this->whenLoaded('like')),
            'favorite' => new FavoriteResource($this->whenLoaded('favorite')),
            'comment' => new CommentResource($this->whenLoaded('comment')),
            'download' => new DownloadResource($this->whenLoaded('download')),
            $this->addCount('likes'),
            $this->addCount('favorites'),
            $this->addCount('comments'),
            $this->addCount('downloads'),
            $this->addCount('beats'),
            $this->addCount('samples'),
            $this->addCount('songs'),
            $this->addCount('designs'),
        ];
    }
}
