<?php

namespace App\Http\Requests;

use App\Enums\BattleStates;
use App\Interfaces\HasBattlesInterface;
use App\Models\Design;
use App\Models\Track;
use App\Rules\EnoughPoints;
use App\Rules\ModelInterfaceRule;
use App\Rules\MustBeOwner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BattleRequest extends FormRequest
{
    public Track|Design|null $model;

    public Track|Design|null $opponent;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->model = morphedModel($this->get('type'), $this->get('id'));
        $this->opponent = morphedModel($this->get('type'), $this->get('opponent_id'));

        if (! $this->model) {
            throw new NotFoundHttpException(trans('exceptions.not_found_data', ['data' => tl('creation')]));
        }

        $this->merge([
            'id' => $this->model?->id,
            'has_battles' => $this->model?->battles()?->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS])?->exists(),
            'can_challenge' => $this->model?->can_challenge,
            'opponent_in_battle' => $this->opponent?->battles()?->areInProgress()?->exists(),
            'battle' => true,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $table = Str::plural($this->get('type'));

        return [
            'id' => new MustBeOwner($table),
            'type' => new ModelInterfaceRule(HasBattlesInterface::class),
            'has_battles' => Rule::in([false]),
            'can_challenge' => Rule::in([true]),
            'opponent_in_battle' => Rule::in([false]),
            'battle' => new EnoughPoints(config('company.points.prices.create_battle')),
            'opponent_id' => [
                'nullable',
                'int',
                Rule::exists($table, 'id')
                    ->whereNot('created_by', auth()->id())
                    ->where('can_challenge', true)
                    ->when($table === 'tracks', fn (Exists $q) => $q->where('track_type', $this->model?->track_type?->value))
                    ->when($table === 'designs', fn (Exists $q) => $q->where('design_type', $this->model?->design_type?->value)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'has_battles.in' => trans('validation.already_in_battle'),
            'can_challenge.in' => trans('validation.cannot_challenge', ['name' => $this->model?->name]),
            'opponent_id.exists' => trans('validation.opponent_not_allowed'),
            'opponent_in_battle.in' => trans('validation.opponent_in_battle'),
        ];
    }
}
