<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @mixin NewAccessToken
 * @mixin PersonalAccessToken
 */
class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof NewAccessToken) {
            return [
                'id' => $this->accessToken->id,
                'name' => $this->accessToken->name,
                'token' => Str::after($this->plainTextToken, '|'),
                'updated_at' => $this->accessToken->updated_at->toDateString(),
                'created_at' => $this->accessToken->created_at->toDateString(),
            ];
        } elseif ($this->resource instanceof PersonalAccessToken) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'last_used_at' => $this->last_used_at?->toDateString(),
                'updated_at' => $this->updated_at?->toDateString(),
                'created_at' => $this->created_at?->toDateString(),
            ];
        }

        return [];
    }
}
