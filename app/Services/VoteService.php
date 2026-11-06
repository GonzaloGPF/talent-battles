<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vote;
use Exception;
use Illuminate\Database\Eloquent\Model;

class VoteService
{
    public function create(int $battleId, Model $model, ?User $user = null): ?Model
    {
        /** @var User $user */
        $user = $user ?? auth()->user();

        if (! $user) {
            return null;
        }

        try {
            return Vote::forceCreate([
                'created_by' => $user->id,
                'battle_id' => $battleId,
                'votable_type' => get_class($model),
                'votable_id' => $model->id,
                'value' => $user->level->getVoteValue(),
            ]);
        } catch (Exception $exception) {
            return null;
        }
    }
}
