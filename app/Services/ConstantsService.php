<?php

namespace App\Services;

use BackedEnum;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ConstantsService
{
    public static int $DAY = 86400; // 86400 seconds = 1 day

    /**
     * @return array<string, list<int|string>>
     *
     * @throws Exception
     */
    public function getConstants(): array
    {
        $locale = app()->getLocale();

        return cache()->remember("constants-$locale", self::$DAY, function () {
            return loadConstants()
                ->mapWithKeys(function (string $enum) {
                    $key = $this->getConstantKey($enum);
                    /** @var array<int, BackedEnum> $cases */
                    $cases = $enum::cases();
                    $values = collect($cases)
                        ->map(fn (BackedEnum $enum) => $enum->value)
                        ->values()
                        ->toArray();

                    return [$key => $values];
                })
                ->toArray();
        });
    }

    /**
     * @return array<string, mixed>|MissingValue
     */
    public static function toArray(?Model $model): array|MissingValue
    {
        if (! $model) {
            return new MissingValue;
        }

        return [
            'id' => $model->getKey(),
            'name' => $model->getAttribute('name'),
            'label' => $model->getAttribute('label'),
        ];
    }

    /**
     * @return array<mixed>|MissingValue
     */
    public static function toResource(Model $model, string $relationship): array|MissingValue
    {
        if (! $model->relationLoaded($relationship)) {
            return new MissingValue;
        }

        $relation = $model->$relationship;

        if ($relation instanceof Collection) {
            return $relation
                ->map(fn (Model $constant) => self::toArray($constant))
                ->toArray();
        }

        return self::toArray($model->$relationship);
    }

    private function getConstantKey(string $className): string
    {
        return Str::of(class_basename($className))
            ->plural()
            ->snake()
            ->value();
    }
}
