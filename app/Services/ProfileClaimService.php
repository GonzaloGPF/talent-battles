<?php

namespace App\Services;

use App\Enums\Roles;
use App\Models\Activity;
use App\Models\Album;
use App\Models\Battle;
use App\Models\Comment;
use App\Models\Design;
use App\Models\Favorite;
use App\Models\Like;
use App\Models\Product;
use App\Models\Track;
use App\Models\User;
use App\Models\Vote;
use App\Notifications\ProfileClaimed;
use App\Notifications\ProfileDeleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Hands a profile created to show the platform working to the producer whose music it holds, or deletes it when they
 * decline. Admins send the link and whoever holds it decides. A profile has one link at a time: a new one replaces it,
 * an admin can revoke it, and it stops working once used. Admins hear about every answer.
 */
class ProfileClaimService
{
    public const LINK_DAYS = 30;

    public function __construct(
        private readonly BattleService $battleService,
        private readonly EmailService $emailService,
    ) {}

    /**
     * A new link, which replaces the one the profile had. The account keeps only the hash of its key.
     *
     * @return array{url: string, expires_at: string}
     */
    public function createLink(User $user): array
    {
        $key = Str::random(40);
        $expiresAt = now()->addDays(self::LINK_DAYS);

        $user->forceFill([
            'claim_token' => hash('sha256', $key),
            'claim_expires_at' => $expiresAt,
        ])->saveQuietly();

        return [
            'url' => URL::temporarySignedRoute('users.claim.show', $expiresAt, ['user' => $user->id, 'key' => $key]),
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    public function revokeLink(User $user): void
    {
        $user->forceFill(['claim_token' => null, 'claim_expires_at' => null])->saveQuietly();
    }

    /**
     * The API links the claim page posts to, with the key and the expiry of the link that opened it.
     *
     * @return array{claim_url: string, decline_url: string}
     */
    public function createActionLinks(User $user, string $key, Carbon $expiresAt): array
    {
        $parameters = ['user' => $user->id, 'key' => $key];

        return [
            'claim_url' => URL::temporarySignedRoute('users.claim.store', $expiresAt, $parameters),
            'decline_url' => URL::temporarySignedRoute('users.claim.destroy', $expiresAt, $parameters),
        ];
    }

    public function isValidKey(User $user, mixed $key): bool
    {
        return is_string($key)
            && $user->claim_token !== null
            && $user->claim_expires_at?->isFuture()
            && hash_equals($user->claim_token, hash('sha256', $key));
    }

    /**
     * @param  array{email: string, password: string}  $data
     */
    public function claim(User $user, array $data): User
    {
        $user->forceFill([
            'email' => $data['email'],
            'password' => $data['password'],
            'token' => Str::random(64),
            'oauth_id' => null,
            'oauth_driver' => null,
            'verified_at' => null,
            'claim_token' => null,
            'claim_expires_at' => null,
        ])->save();

        $this->emailService->createVerifyEmail($user);
        Notification::send($this->admins(), new ProfileClaimed($user));

        return $user;
    }

    /**
     * Deletes the battles of the profile, its creations with their files, what it did on the platform and the personal
     * data of the account. The account stays, anonymized and deleted, because sales and emails may point at it.
     */
    public function decline(User $user): void
    {
        $name = $user->getNickName();

        DB::transaction(function () use ($user) {
            $this->battleService->deleteBattles($this->battlesOf($user));

            /** @var EloquentCollection<int, Track|Design> $creations */
            $creations = Track::where('created_by', $user->id)->get()
                ->concat(Design::where('created_by', $user->id)->get());

            Product::where('created_by', $user->id)
                ->when($creations->isNotEmpty(), fn (Builder $query) => $query->orWhereMorphedTo('orderable', $creations))
                ->get()
                ->each(fn (Product $product) => $product->delete());
            Activity::where('created_by', $user->id)
                ->orWhereMorphedTo('target', $creations->concat([$user]))
                ->delete();
            collect([Comment::class, Like::class, Favorite::class, Vote::class])
                ->each(fn (string $model) => $model::where('created_by', $user->id)->delete());

            $creations->concat([$user])->each(function (Track|Design|User $model) {
                $model->comments()->delete();
                $model->likes()->delete();
                $model->favorites()->delete();
            });
            $creations->each(fn (Track|Design $creation) => $creation->delete());
            Album::where('created_by', $user->id)->get()->each(fn (Album $album) => $album->delete());
            $user->notifications()->delete();

            $this->anonymize($user);
            $user->delete();
        });

        Notification::send($this->admins(), new ProfileDeleted($user, $name));
    }

    /**
     * @return EloquentCollection<int, User>
     */
    private function admins(): EloquentCollection
    {
        return User::where('role', Roles::ADMIN)->get();
    }

    /**
     * @return EloquentCollection<int, Battle>
     */
    private function battlesOf(User $user): EloquentCollection
    {
        return Battle::whereHas('tracks', fn (Builder $query) => $query->where('created_by', $user->id))
            ->orWhereHas('designs', fn (Builder $query) => $query->where('created_by', $user->id))
            ->orWhere('created_by', $user->id)
            ->orWhere('won_by', $user->id)
            ->get();
    }

    private function anonymize(User $user): void
    {
        $user->forceFill([
            'name' => trans('labels.deleted_user'),
            'last_name' => null,
            'nick' => null,
            'email' => "deleted-{$user->id}@talentbattles.invalid",
            'phone' => null,
            'birthdate' => null,
            'gender' => null,
            'quote' => null,
            'bio' => null,
            'skills' => null,
            'categories' => null,
            'address' => null,
            'social_settings' => config('defaults.social_settings'),
            'password' => Str::random(64),
            'token' => Str::random(64),
            'oauth_id' => null,
            'oauth_driver' => null,
            'claim_token' => null,
            'claim_expires_at' => null,
        ])->save();
    }
}
