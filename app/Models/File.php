<?php

namespace App\Models;

use App\Enums\FileTypes;
use App\Interfaces\HasCreatedByInterface;
use App\Observers\FileObserver;
use App\Services\FileService;
use App\Traits\HasCreatedBy;
use App\Traits\IsFilterable;
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
    use Filterable,
        HasCreatedBy,
        HasFactory,
        IsFilterable;

    const PATH_TMP = 'files/tmp';

    protected $fillable = ['name'];

    protected $hidden = ['secret'];

    protected $casts = [
        'created_by' => 'int',
        'fileable_id' => 'int',
        'file_type' => FileTypes::class,
    ];

    protected function name(): Attribute
    {
        return Attribute::set(function ($value) {
            $name = pathinfo($value, PATHINFO_FILENAME);

            return trim(str_replace('.', '', str_replace(' ', '_', $name)));
        });
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

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeAreImage(Builder $builder): Builder
    {
        return $builder->whereIn('mime', FileService::$IMAGE_MIMES);
    }

    public function scopeAreAudio(Builder $builder): Builder
    {
        return $builder->whereIn('mime', FileService::$AUDIO_MIMES);
    }

    public function scopeByTypes(Builder $builder, array $fileTypes): Builder
    {
        return $builder->whereIn('file_type', $fileTypes);
    }
}
