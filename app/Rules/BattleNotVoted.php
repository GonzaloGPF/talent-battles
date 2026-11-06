<?php

namespace App\Rules;

use App\Models\Vote;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BattleNotVoted implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(public ?int $userId = null)
    {
        $this->userId = $userId ?? auth()->id();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $alreadyVoted = Vote::query()
            ->where('created_by', $this->userId)
            ->where('battle_id', $value)
            ->exists();

        if ($alreadyVoted) {
            $fail('validation.already_voted');
        }
    }
}
