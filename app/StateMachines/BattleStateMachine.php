<?php

namespace App\StateMachines;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Events\BattleFinished;
use App\Events\BattleInProgress;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Track;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BattleStateMachine extends StateMachine
{
    public function transitions(): array
    {
        return [
            BattleStates::OPEN->value => [
                BattleStates::IN_PROGRESS->value,
            ],
            BattleStates::IN_PROGRESS->value => [
                BattleStates::FINISHED->value,
            ],
        ];
    }

    /**
     * @param  string  $from
     * @param  string  $to
     * @param  Battle  $model
     */
    public function validatorForTransition($from, $to, $model): ?\Illuminate\Contracts\Validation\Validator
    {
        if ($model->battle_type === BattleTypes::TRACK) {
            $creationIds = $model->tracks->modelKeys();
        } else {
            $creationIds = $model->designs->modelKeys();
        }

        if ($to === BattleStates::IN_PROGRESS->value) {
            if ($model->battle_type === BattleTypes::TRACK) {
                $trackType = Track::find($creationIds[0])->track_type;
                $existsRule = Rule::exists('tracks', 'id')->where('track_type', $trackType->value);
            } else {
                $designType = Design::find($creationIds[0])->design_type;
                $existsRule = Rule::exists('designs', 'id')->where('design_type', $designType->value);
            }

            return Validator::make([
                'creation_ids' => $creationIds,
            ], [
                'creation_ids' => ['array', 'required', 'min:2'],
                'creation_ids.*' => ['required', 'int', $existsRule],
            ]);
        }

        if ($to === BattleStates::FINISHED->value) {
            return Validator::make([
                'creation_ids' => $creationIds,
            ], [
                'creation_ids' => ['array', 'required', 'size:2'],
            ]);
        }

        return null;
    }

    public function afterTransitionHooks(): array
    {
        return [
            BattleStates::IN_PROGRESS->value => [
                function ($from, Battle $battle) {
                    BattleInProgress::dispatch($battle);
                },
            ],
            BattleStates::FINISHED->value => [
                function ($from, Battle $battle) {
                    BattleFinished::dispatch($battle);
                },
            ],
        ];
    }
}
