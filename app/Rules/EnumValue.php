<?php

namespace App\Rules;

use BackedEnum;
use Illuminate\Contracts\Validation\Rule;

class EnumValue implements Rule
{
    private mixed $value;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $enumClass,
        private readonly bool $multiple = false
    ) {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (is_array($value)) {
            $value = array_filter($value);
        }

        $this->value = $value;

        if (empty($value)) {
            return true;
        }

        if (! method_exists($this->enumClass, 'cases')) {
            return false;
        }

        if ($this->multiple && ! is_array($value)) {
            $value = [$value];
        }

        $allowedValues = collect($this->enumClass::cases())
            ->map(fn (BackedEnum $enum) => $enum->value);

        if ($this->multiple) {
            $notAllowedValue = collect($this->value)
                ->filter(fn (string $value) => $allowedValues->doesntContain($value))
                ->first();

            if (! $notAllowedValue) {
                return true;
            }

            $this->value = $notAllowedValue;

            return false;
        } else {
            return $allowedValues->contains($value);
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.invalid', ['value' => $this->multiple ? collect($this->value)->join(', ') : $this->value]);
    }
}
