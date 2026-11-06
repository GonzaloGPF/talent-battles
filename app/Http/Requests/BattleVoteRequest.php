<?php

namespace App\Http\Requests;

use App\Enums\BattleStates;
use App\Interfaces\HasVotesInterface;
use App\Models\Battle;
use App\Models\User;
use App\Rules\BattleNotVoted;
use App\Rules\ModelInterfaceRule;
use App\Rules\MustNotBeOwner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class PointRequest
 *
 * @property Battle $battle
 */
class BattleVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws AuthorizationException
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->verified_at) {
            throw new AuthorizationException(trans('exceptions.unverified_email'));
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

        $this->merge([
            'battle_id' => $this->battle->id,
            $morphsAttributeId => $this->get('model_id'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $morphsAttributeId = getMorphIdAttribute($this->get('model_type'));

        return [
            'model_type' => ['required', new ModelInterfaceRule(HasVotesInterface::class)],
            'model_id' => [
                'required',
                'int',
                Rule::in($this->battle->getCreations()->modelKeys()),
            ],
            'battle_id' => [
                new BattleNotVoted,
                Rule::exists('battles', 'id')->where('battle_state', BattleStates::IN_PROGRESS->value),
            ],
            $morphsAttributeId => new MustNotBeOwner(getMorphTable($this->get('model_type'))),
        ];
    }

    public function messages(): array
    {
        return [
            'model_id.unique' => trans('validation.already_voted'),
            'model_id.in' => trans('validation.own_battle'),
            'battle_id.exists' => trans('validation.wrong_state'),
        ];
    }

    public function getModel(): Model
    {
        return morphedModel($this->get('model_type'), $this->get('model_id'));
    }
}
