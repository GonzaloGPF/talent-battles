<?php

namespace App\Http\Resources;

use App\Models\Track;
use Illuminate\Http\Request;

/**
 * @mixin Track
 */
class TrackResource extends ApiResource
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
            'album_id' => $this->album_id,
            'thematic' => $this->thematic,
            'track_type' => $this->track_type,
            'name' => $this->name,
            'bpm' => $this->bpm,
            'color' => $this->color,
            'duration' => $this->duration,
            'description' => $this->description,
            'can_challenge' => $this->can_challenge,
            'tags' => $this->tags,
            'key' => $this->key,
            'instruments' => $this->instruments,
            'genres' => $this->genres,
            'waveform' => $this->when($request->get('waveform'), $this->waveform),
            'is_promoted' => $this->isPromoted(),
            'is_bought' => $this->isBoughtByMe(),
            'is_exclusive_sold' => $this->isExclusiveSold(),
            'created_at' => $this->created_at?->toISOString(),
            'createdBy' => new UserResource($this->whenLoaded('createdBy')),
            'album' => new AlbumResource($this->whenLoaded('album')),
            'audio' => new FileResource($this->whenLoaded('audio')),
            'image' => new FileResource($this->whenLoaded('image')),
            'victories' => BattleResource::collection($this->whenLoaded('victories')),
            'battles' => BattleResource::collection($this->whenLoaded('battles')),
            'currentBattle' => $this->firstManyToMany('currentBattle', BattleResource::class),
            'openBattle' => $this->firstManyToMany('openBattle', BattleResource::class),
            'like' => new LikeResource($this->whenLoaded('like')),
            'favorite' => new FavoriteResource($this->whenLoaded('favorite')),
            'comment' => new CommentResource($this->whenLoaded('comment')),
            'download' => new DownloadResource($this->whenLoaded('download')),
            'orderLine' => new OrderLineResource($this->whenLoaded('orderLine')),
            'product' => new ProductResource($this->whenLoaded('product')),
            $this->addCount('battles'),
            $this->addCount('victories'),
            $this->addCount('likes'),
            $this->addCount('favorites'),
            $this->addCount('comments'),
            $this->addCount('downloads'),
        ];
    }
}
