<?php

namespace App\StateMachines;

use App\Exceptions\TransitionNotAllowedException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

abstract class StateMachine
{
    public function __construct(public readonly string $field, public readonly Model $model) {}

    /**
     * Allowed transitions, keyed by the state they start from.
     *
     * @return array<string, array<int, string>>
     */
    abstract public function transitions(): array;

    /**
     * Validator that must pass before the transition happens.
     *
     * @param  string  $from
     * @param  string  $to
     * @param  Model  $model
     */
    public function validatorForTransition($from, $to, $model): ?Validator
    {
        return null;
    }

    /**
     * Callbacks to run once the model has been saved, keyed by the state reached.
     *
     * @return array<string, array<int, callable>>
     */
    public function afterTransitionHooks(): array
    {
        return [];
    }

    public function canBe(string $from, string $to): bool
    {
        return in_array($to, $this->transitions()[$from] ?? [], true);
    }

    /**
     * @throws TransitionNotAllowedException
     * @throws ValidationException
     */
    public function transitionTo(string $to): void
    {
        $from = $this->model->{$this->field};

        if ($to === $from) {
            return;
        }

        if (! $this->canBe($from, $to)) {
            throw new TransitionNotAllowedException($from, $to, $this->model::class);
        }

        $validator = $this->validatorForTransition($from, $to, $this->model);

        if ($validator?->fails()) {
            throw new ValidationException($validator);
        }

        $this->model->{$this->field} = $to;
        $this->model->save();

        foreach ($this->afterTransitionHooks()[$to] ?? [] as $hook) {
            $hook($from, $this->model);
        }
    }
}
