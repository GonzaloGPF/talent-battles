<?php

namespace App\Models;

use App\Enums\Categories;
use App\Enums\Gender;
use App\Enums\Levels;
use App\Enums\Roles;
use App\Enums\Skills;
use App\Interfaces\HasCommentsInterface;
use App\Interfaces\HasFavoritesInterface;
use App\Interfaces\HasFilesInterface;
use App\Interfaces\HasLikesInterface;
use App\Interfaces\HasReportsInterface;
use App\Observers\UserObserver;
use App\Traits\Commentable;
use App\Traits\Favoritable;
use App\Traits\HasAddress;
use App\Traits\HasCreations;
use App\Traits\HasFiles;
use App\Traits\HasReports;
use App\Traits\IsFilterable;
use App\Traits\Likeable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Str;

/**
 * @mixin IdeHelperUser
 */
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements HasCommentsInterface, HasFavoritesInterface, HasFilesInterface, HasLikesInterface, HasReportsInterface
{
    use Billable,
        BroadcastsEvents,
        Commentable,
        Favoritable,
        HasAddress,
        HasApiTokens,
        HasCreations,
        HasFactory,
        HasFiles,
        HasRelationships,
        HasReports,
        IsFilterable,
        Likeable,
        Notifiable,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // 'role',
        'name',
        'last_name',
        'nick',
        'birthdate',
        'gender',
        'email',
        'phone',
        'locale',
        'currency',
        'password',
        'quote',
        'bio',
        'address',
        'account',
        'social_settings',
        'notification_settings',
        'skills',
        'categories',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'role' => Roles::class,
        'level' => Levels::class,
        'gender' => Gender::class,
        'birthdate' => 'date',
        'verified_at' => 'datetime',
        'last_donation' => 'datetime',
        'points' => 'float',
        'experience' => 'float',
        'rating' => 'float',
        'address' => 'array',
        'account' => 'array',
        'social_settings' => 'array',
        'notification_settings' => 'array',
        'skills' => AsEnumCollection::class.':'.Skills::class,
        'categories' => AsEnumCollection::class.':'.Categories::class,
        //        'password' => 'encrypted',
    ];

    public function getNickName(): string
    {
        return $this->nick ?? $this->name;
    }

    protected function password(): Attribute
    {
        return Attribute::set(fn ($value) => Hash::make($value));
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn ($value) => \Illuminate\Support\Str::of($value)->trim()->lower()->value());
    }

    protected function notificationSettings(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->parseNotificationSettings(json_decode($value ?? '', true)),
            set: fn ($value) => json_encode($this->parseNotificationSettings($value)),
        );
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    public function feed(): HasMany
    {
        return $this->hasMany(Activity::class, 'receiver_id');
    }

    public function orderLines(): HasManyThrough
    {
        return $this->hasManyThrough(OrderLine::class, Order::class, 'orders.created_by');
    }

    public function createdReports(): HasMany
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function battles(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->tracks(), (new Track)->battles());
    }

    public function favoriteLists(): HasMany
    {
        return $this->hasMany(FavoriteList::class, 'created_by');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'created_by');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'created_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'created_by');
    }

    public function isAccountReady(): bool
    {
        if (empty($this->account)) {
            return false;
        }

        return ! empty($this->account['details_submitted'])
            && ! empty($this->account['charges_enabled'])
            && ! empty($this->account['payouts_enabled']);
    }

    private function parseNotificationSettings($value): array
    {
        if (empty($value) || ! is_array($value)) {
            return [];
        }

        $processed = collect($value)
            ->transform(fn (array $data) => [
                'mail' => boolval($data['mail'] ?? false),
                'broadcast' => boolval($data['broadcast'] ?? false),
            ])
            ->toArray();

        return array_merge(config('defaults.notification_settings'), $processed);
    }

    public function getChannelName(): string
    {
        return "App.Models.User.$this->id";
    }

    public function stripeAddress(): array
    {
        return $this->toStripeAddress();
    }

    public function stripePreferredLocales(): ?array
    {
        $countryCode = Str::upper($this->address['country_code'] ?? '');
        $language = Str::lower($this->locale);

        if (! $language || ! $countryCode) {
            return null;
        }

        return ["{$language}-{$countryCode}"];
    }
}
