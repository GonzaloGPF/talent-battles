<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDOException;

class MustBeOwner implements Rule
{
    protected string $attribute;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(private ?string $table = null)
    {
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
        $this->attribute = $attribute;

        if (! $value) {
            return true;
        }

        if (! $this->table && Str::endsWith($attribute, '_id')) {
            $this->table = $this->getTable($attribute);
        }

        if (! $this->table) {
            return false;
        }

        try {
            return DB::table($this->table)
                ->where('id', $value)
                ->where('created_by', auth()->id())
                ->exists();
        } catch (PDOException $exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.must_be_owner', ['name' => ta($this->attribute)]);
    }

    private function getTable(string $attribute): string
    {
        return Str::of($attribute)
            ->before('_id')
            ->plural()
            ->value();
    }
}
