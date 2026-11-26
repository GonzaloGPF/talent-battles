<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Turns the relations a request asks for (`with`, `counts`) into paths that Eloquent can load safely.
 * Eloquent loads a relation by calling the model method with its name, and runs the value of a keyed
 * entry as a constraint, so only the names of real relation methods may reach it.
 */
class RelationHelper
{
    /**
     * @var array<int, class-string<Model>>|null
     */
    private ?array $modelClasses = null;

    /**
     * The requested paths ("createdBy", "orderable.createdBy") that are relations from the model.
     * Keys, values that are not text, unknown names and paths deeper than the maximum are dropped.
     *
     * @return array<int, string>
     */
    public function requested(Model $model, mixed $requested, int $maxDepth = PHP_INT_MAX): array
    {
        return collect(Arr::wrap($requested))
            ->filter(fn (mixed $path) => is_string($path))
            ->map(fn (string $path) => array_map(fn (string $name) => Str::camel($name), explode('.', $path)))
            ->filter(fn (array $names) => count($names) <= $maxDepth && $this->isPath($model::class, $names))
            ->map(fn (array $names) => implode('.', $names))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $names
     */
    private function isPath(string $modelClass, array $names): bool
    {
        $modelClasses = [$modelClass];

        foreach ($names as $name) {
            $modelClasses = $this->relatedClasses($modelClasses, $name);

            if ($modelClasses === []) {
                return false;
            }
        }

        return true;
    }

    /**
     * The models that the relation leads to from any of the given models. A morph-to relation can lead to any model.
     *
     * @param  array<int, class-string<Model>>  $modelClasses
     * @return array<int, class-string<Model>>
     */
    private function relatedClasses(array $modelClasses, string $name): array
    {
        return collect($modelClasses)
            ->filter(fn (string $modelClass) => $this->isRelationMethod($modelClass, $name))
            ->flatMap(function (string $modelClass) use ($name) {
                $relation = (new $modelClass)->{$name}();

                if ($relation instanceof MorphTo) {
                    return $this->modelClasses();
                }

                return $relation instanceof Relation ? [$relation->getRelated()::class] : [];
            })
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Only a public method declared to return a relation counts, so checking a name never calls any other method.
     *
     * @param  class-string<Model>  $modelClass
     */
    private function isRelationMethod(string $modelClass, string $name): bool
    {
        if (! method_exists($modelClass, $name)) {
            return false;
        }

        $method = new ReflectionMethod($modelClass, $name);
        $returnType = $method->getReturnType();

        return $method->isPublic()
            && ! $method->isStatic()
            && $method->getNumberOfRequiredParameters() === 0
            && $returnType instanceof ReflectionNamedType
            && is_a($returnType->getName(), Relation::class, true);
    }

    /**
     * @return array<int, class-string<Model>>
     */
    private function modelClasses(): array
    {
        if ($this->modelClasses !== null) {
            return $this->modelClasses;
        }

        $this->modelClasses = [];

        foreach (glob(app_path('Models').'/*.php') ?: [] as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');

            if (is_subclass_of($class, Model::class)) {
                $this->modelClasses[] = $class;
            }
        }

        return $this->modelClasses;
    }
}
