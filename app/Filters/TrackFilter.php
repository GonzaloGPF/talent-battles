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

    /**
     * @return TrackFilter
     */
    public function promoted($value): Filter
    {
        if (! $value) {
            return $this;
        }

        return $this->whereHas('product', fn (Builder $q) => $q->where('promoted', '>=', today()));
    }

    public function withoutOpenBattles(): Filter
    {
        return $this->whereDoesntHave('battles', fn (Builder $builder) => $builder->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS]));
    }

    public function canChallenge($value): Builder
    {
        return $this->boolean('can_challenge', $value);
    }

    /**
     * @return Builder
     */
    public function exclusive($value): TrackFilter
    {
        return $this->whereHas('product', fn (Builder $q) => $this->boolean('exclusive', $value, $q));
    }

    /**
     * @return Builder
     */
    public function inSale($value): TrackFilter
    {
        return $value
            ? $this->whereHas('product')
            : $this->whereDoesntHave('product');
    }

    /**
     * @return Builder
     */
    public function sold($value): TrackFilter
    {
        $operator = $value
            ? '>'
            : '=';

        return $this->whereHas('product', fn (Builder $q) => $q->where('sales', $operator, 0));
    }

    public function tags($tags): Builder
    {
        return $this->query->byTags($tags);
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
