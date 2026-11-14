<?php

namespace App\Http\Resources;

use App\Enums\Roles;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @mixin User
 */
class UserResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $itsMe = $this->id === auth()->id();

        return [
            'id' => $this->id,
            'role' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->role),
            'level' => $this->level,
            'model' => $this->addModel(),
            'name' => $this->name,
            'nick' => $this->nick,
            'nick_name' => $this->getNickName(),
            'last_name' => $this->when($itsMe, $this->last_name),
            'birthdate' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->birthdate),
            'gender' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->gender),
            'email' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->email),
            'phone' => $this->when($itsMe, $this->phone),
            'verified_at' => $this->verified_at?->toISOString(),
            'last_donation' => $this->last_donation?->toISOString(),
            'locale' => $this->locale,
            'currency' => $this->currency,
            'quote' => $this->quote,
            'bio' => $this->bio,
            'points' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->points),
            'experience' => $this->experience,
            'rating' => $this->rating,
            'address' => $this->when($itsMe || hasRole(Roles::ADMIN), $this->address),
            'account' => $this->when($itsMe, $this->account),
            'social_settings' => $this->social_settings,
            'notification_settings' => $this->when($itsMe, $this->notification_settings),
            'is_account_ready' => $this->when($itsMe, $this->isAccountReady()),
            'claim_link_expires_at' => $this->when(hasRole(Roles::ADMIN) && $this->claim_token && $this->claim_expires_at?->isFuture(), fn () => $this->claim_expires_at->toISOString()),
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->when((bool) $this->deleted_at, $this->deleted_at?->toISOString()),
            //            'disabled' => $this->disabled?->toISOString(),
            'skills' => $this->skills,
            'categories' => $this->categories,
            'image' => new FileResource($this->whenLoaded('image')),
            'tokens' => TokenResource::collection($this->whenLoaded('tokens')),
            'like' => new LikeResource($this->whenLoaded('like')),
            'favorite' => new FavoriteResource($this->whenLoaded('favorite')),
            'comment' => new CommentResource($this->whenLoaded('comment')),
            'favoriteLists' => FavoriteListResource::collection($this->whenLoaded('favoriteLists')),
            $this->addCount('likes'),
            $this->addCount('favorites'),
            $this->addCount('comments'),
            $this->addCount('beats'),
            $this->addCount('samples'),
            $this->addCount('songs'),
            $this->addCount('designs'),
            $this->addCount('albums'),
        ];
    }
}
