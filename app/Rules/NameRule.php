<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class NameRule implements ValidationRule
{
    const MAX_SIZE = 255;

    const MIN_SIZE = 3;

    const REG = "/[a-zA-Z0-9\-_.\s]+/"; // Alphanumeric with spaces, underscores and dashes

    private array $except;

    private ?string $value;

    const FORBIDDEN_CHARS = [
        '\\',
        '\'',
        '?',
        '¿',
        '!',
        '¡',
        '`',
        '´',
        ':',
        '=',
        '"',
        '·',
        '$',
        '%',
        //        '&', Hay nombres que utilizan este símbolo
        '/',
        'º',
        'ª',
        '<',
        '>',
        '*',
        '+',
        '~',
        '@',
        '|',
        '#',
        '¬',
    ];

    public function __construct(array $except = [])
    {
        $this->except = $except;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $this->value = $value;

        $forbidden = collect(self::FORBIDDEN_CHARS)
            ->filter(fn (string $char) => ! in_array($char, $this->except))
            ->toArray();

        if (Str::contains($value, $forbidden)) {
            $fail('validation.invalid')->translate(['value' => $this->value]);
        }

        if (! preg_match(self::REG, $value)) {
            $fail('validation.invalid')->translate(['value' => $this->value]);
        }

        if (mb_strlen($value) < self::MIN_SIZE || mb_strlen($value) > self::MAX_SIZE) {
            $fail('validation.between.string')->translate([
                'attribute' => $attribute,
                'min' => self::MIN_SIZE,
                'max' => self::MAX_SIZE,
            ]);
        }
    }
}
