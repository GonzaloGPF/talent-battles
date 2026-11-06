<?php

namespace App\Http\Requests;

use App\Enums\BattleStates;
use App\Models\Battle;
use App\Models\Design;
use App\Rules\MustBeOwner;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Class DesignBattleRequest
 *
 * @property Design $design
 */
class DesignBattleRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'design_id' => $this->design->id,
            'has_battles' => $this->design->battles()->whereIn('battle_state', [BattleStates::OPEN, BattleStates::IN_PROGRESS])->exists(),
            'can_challenge' => $this->design->can_challenge,
            'opponent_in_battle' => Design::find($this->get('opponent_id'))?->battles()->areInProgress()->exists(),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @throws AuthorizationException
     */
    public function authorize(): bool
    {
        return (bool) Gate::authorize('create', Battle::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'design_id' => new MustBeOwner,
            'has_battles' => Rule::in([false]),
            'can_challenge' => Rule::in([true]),
            'opponent_in_battle' => Rule::in([false]),
            'opponent_id' => [
                'nullable',
                'int',
                Rule::exists('designs', 'id')
                    ->whereNot('created_by', auth()->id())
                    ->where('can_challenge', true)
                    ->where('design_type', $this->design->design_type->value),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'has_battles.in' => trans('validation.already_in_battle'),
            'can_challenge.in' => trans('validation.cannot_challenge', ['name' => $this->design->name]),
            'opponent_id.exists' => trans('validation.opponent_not_allowed'),
            'opponent_in_battle.in' => trans('validation.opponent_in_battle'),
        ];
    }
}
