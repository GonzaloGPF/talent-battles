<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    /**
     * What UserResource only shows to the user and to admins, and the secrets it never shows.
     *
     * @var array<int, string>
     */
    protected array $privateColumns = [
        'role', 'last_name', 'email', 'phone', 'birthdate', 'gender', 'points', 'address', 'account',
        'notification_settings', 'password', 'two_factor_secret', 'two_factor_recovery_codes', 'token',
        'remember_token', 'oauth_id', 'oauth_driver', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at',
        'claim_token', 'claim_expires_at',
    ];

    public function email($value): Builder
    {
        $this->where('email', 'like', "%$value%");

        return $this->query;
    }

    public function verified($value): Builder
    {
        $this->nullable('verified_at', $value);

        return $this->query;
    }

    public function skills($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('skills', 'like', "%$value%"));

        return $this->query;
    }

    public function categories($values): Builder
    {
        collect($values)
            ->each(fn (string $value) => $this->where('categories', 'like', "%$value%"));

        return $this->query;
    }

    public function nick($value): Builder
    {
        $this->where('nick', 'like', "%$value%");

        return $this->query;
    }

    public function nickName($value): Builder
    {
        $this->where(function (Builder $builder) use ($value) {
            $builder->where('name', 'like', "%$value%")
                ->orWhere('nick', 'like', "%$value%");
        });

        return $this->query;
    }
}
