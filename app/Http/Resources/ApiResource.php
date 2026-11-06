<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Str;

class ApiResource extends JsonResource
{
    /**
     * Function to apply when the Resource has a ManyToMany Relationship
     * If the relation is loaded, it returns a MergeValue with two values, one for the Collection and other for the ids.
     * For example, if a User has a manyToMany with Role, you can use:
     *  $this->conditionalRelation('roles')
     * And it will merge:
     *  [
     *      'languages' => RoleResource::collection($this->whenLoaded('languages')),
     *      'language_ids => $this->roles->pluck('id')->toArray(),
     *  ]
     */
    public function manyToMany(string $relation, ?string $resourceClass = null): MergeValue|MissingValue
    {
        if (! $this->resource->relationLoaded($relation)) {
            return new MissingValue;
        }

        $resourceName = $resourceClass
            ? class_basename($resourceClass)
            : ucfirst(Str::singular($relation)).'Resource'; // calculating by convention, roles => RoleResource

        $resourceClassName = "App\Http\Resources\\$resourceName";

        if (! class_exists($resourceClassName)) {
            return new MissingValue;
        } else {
            $resourceData = $resourceClassName::collection($this->{$relation});
        }

        return new MergeValue([
            Str::camel($relation) => $resourceData,
            Str::snake(Str::singular($relation)).'_ids' => $this->{$relation}->pluck('id')->toArray(),
        ]);
    }

    public function pivotTable(string $pivot, array $attributes): MergeValue|MissingValue
    {
        if (count($attributes) == 0) {
            return new MissingValue;
        }
        $pivot = Str::snake($pivot);

        return $this->whenPivotLoaded($pivot, function () use ($pivot, $attributes) {
            return new MergeValue([
                $pivot => collect($attributes)
                    ->map(function (string $attribute) {
                        return [
                            'key' => $attribute,
                            'value' => $this->resource->pivot->$attribute,
                        ];
                    })->pluck('value', 'key')
                    ->toArray(),
            ]);
        });
    }

    /**
     * It adds counts attributes with the form of {relation}_count applied for n:m or 1:n relationships
     * If the model has an n:m relationship, for example, like $user->roles
     * then it will add a roles_count attribute to the Resource
     */
    public function addCount(string $relation): MergeValue|MissingValue
    {
        $attribute = Str::snake($relation).'_count';

        if (is_null($this->$attribute)) {
            return new MissingValue;
        }

        return new MergeValue([
            $attribute => intval($this->$attribute),
        ]);
    }

    public function morphedResource(string $relation, ?string $resourceClass = null): MissingValue|JsonResource
    {
        $relation = Str::camel($relation); // A bit of normalization

        if ($resourceClass) {
            return new $resourceClass($this->whenLoaded($relation));
        }

        $attribute = "{$relation}_type";

        $morphedClass = class_basename($this->$attribute);

        if (! class_exists("App\\Models\\$morphedClass")) {
            return new MissingValue;
        }

        $resource = "App\\Http\\Resources\\{$morphedClass}Resource";

        return new $resource($this->whenLoaded($relation));
    }

    public function addModel(): string
    {
        return Str::lower(class_basename($this->resource));
    }

    public function firstManyToMany(string $relationship, string $resource): JsonResource|MissingValue
    {
        if (! $this->resource->relationLoaded($relationship)) {
            return new MissingValue;
        }

        return new $resource($this->resource->$relationship->first());
    }
}
