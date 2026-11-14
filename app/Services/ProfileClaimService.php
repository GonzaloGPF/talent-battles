<?php

namespace App\Services;

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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Hands a profile created to show the platform working to the producer whose music it holds, or deletes it when they
 * decline. Admins send the link; whoever holds it decides, so it stops working once the profile changes hands.
 */
class ProfileClaimService
{
    public const LINK_DAYS = 30;

    public function __construct(
        private readonly BattleService $battleService,
        private readonly EmailService $emailService,
    ) {}

    /**
     * @return array{url: string, expires_at: string}
     */
    public function createLink(User $user): array
    {
        $expiresAt = now()->addDays(self::LINK_DAYS);

        return [
            'url' => URL::temporarySignedRoute('users.claim.show', $expiresAt, $this->linkParameters($user)),
            'expires_at' => $expiresAt->toISOString(),
        ];
    }

    /**
     * The API links the claim page posts to, expiring with the link that opened it.
     *
     * @return array{claim_url: string, decline_url: string}
     */
    public function createActionLinks(User $user, Carbon $expiresAt): array
    {
        return [
            'claim_url' => URL::temporarySignedRoute('users.claim.store', $expiresAt, $this->linkParameters($user)),
            'decline_url' => URL::temporarySignedRoute('users.claim.destroy', $expiresAt, $this->linkParameters($user)),
        ];
    }

    /**
     * The key changes with the email and the password, so a link only works until the profile is claimed.
     */
    public function isValidKey(User $user, mixed $key): bool
    {
        return is_string($key) && hash_equals($this->key($user), $key);
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
        ])->save();

        $this->emailService->createVerifyEmail($user);

        return $user;
    }

    /**
     * Deletes the battles of the profile, its creations with their files, what it did on the platform and the personal
     * data of the account. The account stays, anonymized and deleted, because sales and emails may point at it.
     */
    public function decline(User $user): void
    {
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
    }

    /**
     * @return array{user: int, key: string}
     */
    private function linkParameters(User $user): array
    {
        return ['user' => $user->id, 'key' => $this->key($user)];
    }

    private function key(User $user): string
    {
        return Str::limit(hash_hmac('sha256', $user->email.'|'.$user->password, config('app.key')), 32, '');
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
        ])->save();
    }
}
