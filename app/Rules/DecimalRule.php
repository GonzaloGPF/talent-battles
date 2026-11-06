<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DecimalRule implements ValidationRule
{
    private $value;

    private $message;

    public static int $MAX_DECIMAL_VALUE = 999999;

    private bool $required;

    /**
     * Create a new rule instance.
     */
    public function __construct(bool $required = false)
    {
        $this->required = $required;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->value = $value;

        if (! $this->required && ! $value) {
            return;
        }

        if (! is_numeric($value)) {
            $fail('validation.numeric')->translate(['attribute' => ta($attribute)]);
        }

        if ($value > self::$MAX_DECIMAL_VALUE) {
            $fail('validation.invalid')->translate(['value' => $this->value]);
        }
    }
}
