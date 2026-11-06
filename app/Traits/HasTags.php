<?php

namespace App\Traits;

use App\Interfaces\HasTagsInterface;
use App\Services\TagService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasTags
{
    public function initializeHasTags(): void
    {
        $this->casts += ['tags' => 'array'];
        $this->fillable[] = 'tags';
    }

    public static function bootHasTags(): void
    {
        static::created(function (HasTagsInterface $model) {
            resolve(TagService::class)->saveTags($model->tags);
        });
    }

    protected function tags(): ?Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode(resolve(TagService::class)->sanitizeTags($value))
        );
    }

    public function scopeByTags(Builder $builder, string|array $values): Builder
    {
        if (is_array($values)) {
            $builder->where(function (Builder $q) use ($values) {
                $values = collect($values);

                $firstTag = $values->shift();

                $q->where('tags', 'like', "%$firstTag%");

                $values
                    ->each(fn ($tag) => $q->orWhere('tags', 'like', "%$tag%"));
            });
        } else {
            $builder->where('tags', 'like', "%$values%");
        }

        return $builder;
    }
}
