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
     * @throws Exception
     */
    public function getConstants(): array
    {
        $locale = app()->getLocale();

        return cache()->remember("constants-$locale", self::$DAY, function () {
            return loadConstants()
                ->mapWithKeys(function (string $enum) {
                    $key = $this->getConstantKey($enum);
                    $values = collect($enum::cases())
                        ->map(fn (BackedEnum $enum) => $enum->value)
                        ->values()
                        ->toArray();

                    return [$key => $values];
                })
                ->toArray();
        });
    }

    public static function toArray(?Model $model): array|MissingValue
    {
        if (! $model) {
            return new MissingValue;
        }

        return [
            'id' => $model->id,
            'name' => $model->name,
            'label' => $model->label,
        ];
    }

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
