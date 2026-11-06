<?php

namespace App\Filters;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use Illuminate\Database\Eloquent\Builder;

class BattleFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function inProgress($value): Builder
    {
        $operator = $value
            ? '='
            : '!=';

        $this->where('battle_state', $operator, BattleStates::IN_PROGRESS);

        return $this->query;
    }

    public function isFinished($value): Builder
    {
        $operator = $value
            ? '='
            : '!=';

        $this->where('battle_state', $operator, BattleStates::FINISHED);

        return $this->query;
    }

    public function isNext($value): Builder
    {
        if (! $value) {
            return $this->query;
        }

        $this->where('battle_state', BattleStates::IN_PROGRESS)
            ->whereDoesntHave('votes', fn (Builder $builder) => $builder->where('created_by', auth()->id()))
            ->where(function (Builder $builder) {
                $builder->where(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::TRACK)->has('tracks', 2)->whereDoesntHave('tracks', fn (Builder $q) => $q->where('created_by', auth()->id())))
                    ->orWhere(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::DESIGN)->has('designs', 2)->whereDoesntHave('designs', fn (Builder $q) => $q->where('created_by', auth()->id())));
            })->inRandomOrder();

        return $this->query;
    }

    public function name($name): Builder
    {
        $this->where(function (Builder $builder) use ($name) {
            $builder->where(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::TRACK)->whereHas('tracks', fn (Builder $q) => $q->where('name', 'like', "%$name%")))
                ->orWhere(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::DESIGN)->whereHas('designs', fn (Builder $q) => $q->where('name', 'like', "%$name%")));
        });

        return $this->query;
    }

    public function won($value): Builder
    {
        $this->where('battle_state', BattleStates::FINISHED);

        $value
            ? $this->whereHas('winner', fn (Builder $builder) => $builder->where('created_by', auth()->id()))
            : $this->whereHas('winner', fn (Builder $builder) => $builder->whereNot('created_by', auth()->id()));

        return $this->query;
    }

    public function wonBy($ids): Builder
    {
        $this->whereHas('winner', fn (Builder $builder) => $builder->whereIn('created_by', $ids));

        return $this->query;
    }

    public function lostBy($ids): Builder
    {
        return $this->query
            ->byParticipants($ids)
            ->whereHas('winner', fn (Builder $builder) => $builder->whereNotIn('created_by', $ids));
    }

    public function byParticipants($ids): Builder
    {
        return $this->query->byParticipants($ids);
    }

    public function battleStates($values): Builder // TODO: remove this when StateMachine supports enums (and could cast battle_state correctly for automatic enum filtering)
    {
        $this->whereIn('battle_state', $values);

        return $this->query;
    }
}
