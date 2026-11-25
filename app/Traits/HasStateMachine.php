<?php

namespace App\Traits;

use App\Exceptions\TransitionNotAllowedException;
use BackedEnum;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

use function Illuminate\Support\enum_value;

/**
 * Trait HasStateMachine
 *
 * @mixin Model
 */
trait HasStateMachine
{
    /**
     * @throws AuthorizationException
     */
    public function changeState(string|BackedEnum $state, array $attributes = []): Model
    {
        $state = enum_value($state);
        $property = array_keys($this->stateMachines)[0];

        if (empty($this->$property) || $state === enum_value($this->$property)) {
            return $this;
        }

        try {
            if (! empty($attributes)) {
                $this->forceFill($attributes);
            }

            (new $this->stateMachines[$property]($property, $this))->transitionTo($state);
        } catch (Exception $exception) {
            if ($exception instanceof TransitionNotAllowedException) {
                throw new AuthorizationException(trans('exceptions.illegal_transition', [
                    'from' => tConstValue($property, $this->$property),
                    'to' => tConstValue($property, $state),
                ]));
            }

            throw $exception;
        }

        return $this->fresh();
    }
}
