<?php

namespace App\Services;

use App\Api\StripeApi;
use App\Enums\BattleStates;
use App\Enums\Levels;
use App\Enums\Roles;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as OAuthUser;

class UserService
{
    public static array $groups = [
        'internals' => [Roles::ADMIN, Roles::MANAGER],
        'externals' => [Roles::USER],
        'system' => [Roles::ADMIN],
        'managers' => [Roles::MANAGER],
        'users' => [Roles::USER],
    ];

    private StripeApi $stripeApi;

    public function __construct(StripeApi $stripeApi)
    {
        $this->stripeApi = $stripeApi;
    }

    public function create(array $data): User
    {
        $user = new User($data);
        $user->forceFill([
            'role' => $data['role'] ?? Roles::USER,
            'level' => Levels::NOVICE,
            'password' => $data['password'] ?? Str::random(),
            'token' => $this->newToken(),
            'experience' => 0,
        ])->save();

        return $user;
    }

    public function createFromOAuth(string $driver, OAuthUser $oAuthUser): ?User // TODO: test it
    {
        $user = User::firstWhere('email', $oAuthUser->getEmail());

        if ($user) {
            $user->forceFill([
                'oauth_id' => $oAuthUser->getId(),
                'oauth_driver' => $driver,
            ])->saveQuietly();
        } else {
            $user = User::forceCreate([
                'name' => $oAuthUser->getName(),
                'email' => $oAuthUser->getEmail(),
                'nick' => $oAuthUser->getNickname(),
                'oauth_id' => $oAuthUser->getId(),
                'oauth_driver' => $driver,
                'password' => $this->newToken(),
                'token' => $this->newToken(),
                'verified_at' => now(),
            ]);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function getUserData(?User $user = null): ?User
    {
        return $user ?? auth()->user();
    }

    public function getJWTData(User $user): array
    {
        return [
            'token' => Str::after($user->createToken($user->email)->plainTextToken, '|'),
            'token_type' => 'bearer',
            'user' => new UserResource($user->load('image')),
        ];
    }

    public function loadRelations(User $user): User
    {
        $user->load(['image', 'favoriteLists']);

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function setAccount(?User $user = null): User
    {
        $user = $user ?? auth()->user();

        if (! empty($user->account['id'])) {
            $account = $this->stripeApi->getAccount($user);
        } else {
            $account = $this->stripeApi->createAccount($user);
        }

        $user->forceFill(['account' => $account?->toArray()])
            ->save();

        return $user;
    }

    public function getLevel(?float $experience = 0): ?string
    {
        return collect(config('company.progress'))
            ->filter(fn (int $progress) => $experience >= $progress)
            ->keys()
            ->last();
    }

    public function updateRating(User $user): User
    {
        $user->forceFill([
            'rating' => $this->getRating($user),
        ])->save();

        return $user;
    }

    public function setToken(User $user): User
    {
        $user->token = $this->newToken();
        $user->save();

        return $user;
    }

    public function getRating(User $user): float
    {
        $total = $user->battles()
            ->where('battle_state', BattleStates::FINISHED)
            ->count();

        if (! $total) {
            return 0;
        }

        $wonCount = $user->battles()
            ->where('won_by', $user->id)
            ->count();

        return round($wonCount / $total, 2) * 100;
    }

    public function getRedirectToBillingPortal(User $user): string
    {
        if (! $user->hasStripeId()) {
            resolve(StripeApi::class)->createOrGetStripeCustomer($user);
            $user->fresh();
        }

        return $user->redirectToBillingPortal(url('profile/account'));
    }

    public function syncStripeDetails(User $user): void
    {
        $user->syncStripeCustomerDetails();
    }

    /**
     * Tokens go in the links to verify an email and to reset a password, so nobody may guess them.
     */
    private function newToken(): string
    {
        return Str::random(64);
    }
}
