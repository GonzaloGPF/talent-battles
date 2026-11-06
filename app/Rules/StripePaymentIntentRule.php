<?php

namespace App\Rules;

use App\Api\StripeApi;
use Illuminate\Contracts\Validation\Rule;

class StripePaymentIntentRule implements Rule
{
    private string $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  string  $value
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        return (bool) resolve(StripeApi::class)->retrievePaymentIntent($value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.invalid', ['value' => $this->attribute]);
    }
}
