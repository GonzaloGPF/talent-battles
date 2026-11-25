<?php

namespace App\Filters;

use App\Enums\BattleStates;
use Illuminate\Database\Eloquent\Builder;

class DesignFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array<string, array<array-key, string>>
     */
    public $relations = [
        'album' => [
            'album' => 'ids',
        ],
    ];

    public function promoted(mixed $value): void
    {
        if ($value) {
            $this->whereHas('product', fn (Builder $q) => $q->where('promoted', '>=', today()));
        }
    }

    public function withoutOpenBattles(): void
    {
        $this->whereDoesntHave('battles', fn (Builder $builder) => $builder->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS]));
    }

    public function exclusive(mixed $value): void
    {
        $this->whereHas('product', fn (Builder $q) => $this->boolean('exclusive', $value, $q));
    }

    public function inSale(mixed $value): void
    {
        $value
            ? $this->whereHas('product')
            : $this->whereDoesntHave('product');
    }

    public function sold(mixed $value): void
    {
        $operator = $value
            ? '>'
            : '=';

        $this->whereHas('product', fn (Builder $q) => $q->where('sales', $operator, 0));
    }

    public function canChallenge(mixed $value): void
    {
        $this->boolean('can_challenge', $value);
    }

    public function tags(mixed $tags): void
    {
        $this->query->scopes(['byTags' => [$tags]]);
    }
}
