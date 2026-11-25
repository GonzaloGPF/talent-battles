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
     * @var array<string, array<array-key, string>>
     */
    public $relations = [];

    public function inProgress(mixed $value): void
    {
        $operator = $value
            ? '='
            : '!=';

        $this->where('battle_state', $operator, BattleStates::IN_PROGRESS);
    }

    public function isFinished(mixed $value): void
    {
        $operator = $value
            ? '='
            : '!=';

        $this->where('battle_state', $operator, BattleStates::FINISHED);
    }

    public function isNext(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $this->where('battle_state', BattleStates::IN_PROGRESS)
            ->whereDoesntHave('votes', fn (Builder $builder) => $builder->where('created_by', auth()->id()))
            ->where(function (Builder $builder) {
                $builder->where(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::TRACK)->has('tracks', '=', 2)->whereDoesntHave('tracks', fn (Builder $q) => $q->where('created_by', auth()->id())))
                    ->orWhere(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::DESIGN)->has('designs', '=', 2)->whereDoesntHave('designs', fn (Builder $q) => $q->where('created_by', auth()->id())));
            })->inRandomOrder();
    }

    public function name(mixed $name): void
    {
        $this->where(function (Builder $builder) use ($name) {
            $builder->where(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::TRACK)->whereHas('tracks', fn (Builder $q) => $q->where('name', 'like', "%$name%")))
                ->orWhere(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::DESIGN)->whereHas('designs', fn (Builder $q) => $q->where('name', 'like', "%$name%")));
        });
    }

    public function won(mixed $value): void
    {
        $this->where('battle_state', BattleStates::FINISHED);

        $value
            ? $this->whereHas('winner', fn (Builder $builder) => $builder->where('created_by', auth()->id()))
            : $this->whereHas('winner', fn (Builder $builder) => $builder->whereNot('created_by', auth()->id()));
    }

    public function wonBy(mixed $ids): void
    {
        $this->whereHas('winner', fn (Builder $builder) => $builder->whereIn('created_by', $ids));
    }

    public function lostBy(mixed $ids): void
    {
        $this->query
            ->scopes(['byParticipants' => [$ids]])
            ->whereHas('winner', fn (Builder $builder) => $builder->whereNotIn('created_by', $ids));
    }

    public function byParticipants(mixed $ids): void
    {
        $this->query->scopes(['byParticipants' => [$ids]]);
    }
}
