<?php

namespace App\Filters;

use App\Enums\BattleStates;
use Illuminate\Database\Eloquent\Builder;

class TrackFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [
        'album' => [
            'album' => 'ids',
        ],
    ];

    public function promoted($value): Builder
    {
        if ($value) {
            $this->whereHas('product', fn (Builder $q) => $q->where('promoted', '>=', today()));
        }

        return $this->query;
    }

    public function withoutOpenBattles(): Builder
    {
        $this->whereDoesntHave('battles', fn (Builder $builder) => $builder->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS]));

        return $this->query;
    }

    public function canChallenge($value): Builder
    {
        return $this->boolean('can_challenge', $value);
    }

    public function exclusive($value): Builder
    {
        $this->whereHas('product', fn (Builder $q) => $this->boolean('exclusive', $value, $q));

        return $this->query;
    }

    public function inSale($value): Builder
    {
        $value
            ? $this->whereHas('product')
            : $this->whereDoesntHave('product');

        return $this->query;
    }

    public function sold($value): Builder
    {
        $operator = $value
            ? '>'
            : '=';

        $this->whereHas('product', fn (Builder $q) => $q->where('sales', $operator, 0));

        return $this->query;
    }

    public function tags($tags): Builder
    {
        return $this->query->scopes(['byTags' => [$tags]]);
    }

    public function genres($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('genres', 'like', "%$value%"));

        return $this->query;
    }

    public function instruments($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('instruments', 'like', "%$value%"));

        return $this->query;
    }
}
