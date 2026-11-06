<?php

namespace App\Models;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\HasFavoritesInterface;
use App\Interfaces\HasLikesInterface;
use App\StateMachines\BattleStateMachine;
use App\Traits\Commentable;
use App\Traits\Favoritable;
use App\Traits\HasCreatedBy;
use App\Traits\HasStateMachine;
use App\Traits\IsFilterable;
use App\Traits\Likeable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @property Collection $creations
 *
 * @mixin IdeHelperBattle
 */
class Battle extends Model implements HasCommentsInterface, HasCreatedByInterface, HasFavoritesInterface, HasLikesInterface
{
    use Commentable,
        Favoritable,
        HasCreatedBy,
        HasFactory,
        HasStateMachine,
        IsFilterable,
        Likeable;

    protected $fillable = [];

    protected $casts = [
        //        'battle_state' => BattleStates::class, // TODO: uncomment it when StateMachines offers support to enums fields
        'battle_type' => BattleTypes::class,
        'won_by' => 'int',
    ];

    public array $stateMachines = [
        'battle_state' => BattleStateMachine::class,
    ];

    public function isTrack(): bool
    {
        return $this->battle_type === BattleTypes::TRACK;
    }

    public function isDesign(): bool
    {
        return $this->battle_type === BattleTypes::DESIGN;
    }

    protected function title(): Attribute
    {
        return Attribute::set(fn ($value) => $value ? Str::title(trim($value)) : null);
    }

    public function winner(): MorphTo
    {
        return $this->morphTo('winner');
    }

    //    /**
    //     * @return BelongsToMany|null
    //     */
    //    public function creations(): ?BelongsToMany
    //    {
    //        if ($this->isTrack()) {
    //            return $this->tracks();
    //        }
    //
    //        if ($this->isDesign()) {
    //            return $this->designs();
    //        }
    //
    //        return null;
    //    }

    public function getCreations(): Collection
    {
        if ($this->battle_type === BattleTypes::TRACK) {
            return $this->tracks;
        }

        return $this->designs;
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class);
    }

    public function designs(): BelongsToMany
    {
        return $this->belongsToMany(Design::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeAreOpen(Builder $builder): Builder
    {
        return $builder->where('battle_state', BattleStates::OPEN);
    }

    public function scopeAreInProgress(Builder $builder): Builder
    {
        return $builder->where('battle_state', BattleStates::IN_PROGRESS);
    }

    public function wonBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'won_by');
    }

    public function scopeByParticipants(Builder $builder, int|array $userIds): Builder
    {
        if (! is_array($userIds)) {
            $userIds = [$userIds];
        }

        $userIds = array_filter($userIds);

        return $builder->where(function (Builder $q) use ($userIds) {
            $q->where(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::TRACK)->whereHas('tracks', fn (Builder $q) => $q->whereIn('created_by', $userIds)))
                ->orWhere(fn (Builder $sub) => $sub->where('battle_type', BattleTypes::DESIGN)->whereHas('designs', fn (Builder $q) => $q->whereIn('created_by', $userIds)));
        });
    }

    public function isDraw(): bool
    {
        $pointValues = $this->getCreations()
            ->map(fn (Track|Design $creation) => $creation->getTotalPoints($this->id));

        if ($pointValues->count() < 2) {
            return false;
        }

        return $pointValues->get(0) === $pointValues->get(1);
    }

    public function getWinningCreation(): ?Model
    {
        if ($this->isDraw()) {
            return null;
        }

        return $this->getCreations()
            ->sortByDesc(fn (Track|Design $creation) => $creation->getTotalPoints($this->id))
            ->first();
    }

    /**
     * @return Collection<User>
     */
    public function getParticipants(): Collection
    {
        return $this->getCreations()
            ->map(fn (HasCreatedByInterface $creation) => $creation->createdBy)
            ->filter();
    }

    public function getVoters(): Collection
    {
        return $this->votes->map->createdBy;
    }
}
