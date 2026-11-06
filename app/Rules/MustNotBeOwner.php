<?php

namespace App\Rules;

class MustNotBeOwner extends MustBeOwner
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        return ! parent::passes($attribute, $value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.must_not_be_owner', ['name' => ta($this->attribute)]);
    }
}
