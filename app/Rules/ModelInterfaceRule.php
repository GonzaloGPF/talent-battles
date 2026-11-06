<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use ReflectionClass;
use ReflectionException;

class ModelInterfaceRule implements Rule
{
    private string $interfaceClass;

    private $value;

    private string $attribute;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $interfaceClass)
    {
        $this->interfaceClass = $interfaceClass;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $this->value = $value;

        if (! $value) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass(classByString($value));

            return $reflection->implementsInterface($this->interfaceClass);
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.invalid', ['value' => $this->value]).' ['.$this->attribute.']';
    }
}
