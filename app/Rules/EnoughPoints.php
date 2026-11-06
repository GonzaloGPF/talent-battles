<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class EnoughPoints implements ValidationRule
{
    private float $userPoints;

    public function __construct(private readonly int $requiredPoints)
    {
        /** @var User $user */
        $user = auth()->user();

        $this->userPoints = $user?->points ?? 0;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->userPoints < $this->requiredPoints) {
            $fail('validation.not_enough_points_data')->translate([
                'points' => $this->userPoints,
                'needed' => $this->requiredPoints,
            ]);
        }
    }
}
