<?php

namespace App\Models;

use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Interfaces\HasCreatedByInterface;
use App\Observers\FileObserver;
use App\Services\FileService;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
use Database\Factories\FileFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperFile
 */
#[ObservedBy(FileObserver::class)]
class File extends Model implements HasCreatedByInterface
{
    /** @use HasFactory<FileFactory> */
    use Filterable,
        HasCreatedBy,
        HasFactory,
        IsFilterable;

    const PATH_TMP = 'files/tmp';

    /**
     * What a product sells, what a buyer bought and the evidence of a report: only their owner,
     * the admins and whoever has the secret link of the file may download them.
     *
     * @var array<int, class-string<Model>>
     */
    const PRIVATE_FILEABLE_TYPES = [Product::class, OrderLine::class, Report::class];

    protected $fillable = ['name'];

    protected $hidden = ['secret'];

    protected $casts = [
        'created_by' => 'int',
        'fileable_id' => 'int',
        'file_type' => FileTypes::class,
    ];

    /**
     * @return Attribute<string, string>
     */
    protected function name(): Attribute
    {
        return Attribute::set(function ($value) {
            $name = pathinfo($value, PATHINFO_FILENAME);

            return trim(str_replace('.', '', str_replace(' ', '_', $name)));
        });
    }

    public function isPrivate(): bool
    {
        return in_array($this->fileable_type, self::PRIVATE_FILEABLE_TYPES, true);
    }

    public function fullName(): string
    {
        if (! empty(pathinfo($this->name, PATHINFO_EXTENSION))) {
            return $this->name;
        }

        return $this->name.'.'.$this->getExtension();
    }

    public function getExtension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function toBase64(): ?string
    {
        if (! Storage::exists($this->path)) {
            return null;
        }

        return "data:$this->mime;base64,".base64_encode(Storage::get($this->path));
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    public function scopeAreImage(Builder $builder): Builder
    {
        return $builder->whereIn('mime', FileService::$IMAGE_MIMES);
    }

    /**
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    public function scopeAreAudio(Builder $builder): Builder
    {
        return $builder->whereIn('mime', FileService::$AUDIO_MIMES);
    }

    /**
     * @param  Builder<self>  $builder
     * @param  list<FileTypes|string>  $fileTypes
     * @return Builder<self>
     */
    public function scopeByTypes(Builder $builder, array $fileTypes): Builder
    {
        return $builder->whereIn('file_type', $fileTypes);
    }

    /**
     * The public files and the private files of the user, or every file for an admin.
     *
     * @param  Builder<self>  $builder
     * @return Builder<self>
     */
    public function scopeVisibleTo(Builder $builder, ?User $user): Builder
    {
        if ($user && hasRole(Roles::ADMIN, user: $user)) {
            return $builder;
        }

        return $builder->where(fn (Builder $query) => $query
            ->whereNotIn('fileable_type', self::PRIVATE_FILEABLE_TYPES)
            ->when($user, fn (Builder $query, User $user) => $query->orWhere('created_by', $user->id)));
    }
}
