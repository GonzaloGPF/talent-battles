<?php

namespace App\Filters;

use App\Enums\Roles;
use App\Interfaces\FilterableMorph;
use App\Interfaces\HasCreatedByInterface;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionMethod;

class Filter extends ModelFilter
{
    private const MIN_PREFIX = 'min_'; // Prefix used by front to indicate a min value in a range filter

    private const MAX_PREFIX = 'max_'; // Prefix used by front to indicate a max value in a range filter

    private const NOT_PREFIX = 'not_'; // Prefix used by front to indicate excluded values

    private const WITH = 'with'; // Input key to specify eager load relations

    private const COUNT = 'counts'; // Input key to specify counting relations

    private const RANDOMIZE = 'randomize'; // Applies random order

    private const SORT = 'sort'; // Sorting

    private const LIMIT = 'limit'; // Limit results

    /**
     * Columns only admins may filter or sort by, because the API only shows them to their owner and to admins.
     *
     * @var array<int, string>
     */
    protected array $privateColumns = [];

    /**
     * @var array<string, mixed>
     */
    protected $input;

    /**
     * @var array<int, string>
     */
    private array $enumColumns;

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $input
     */
    public function __construct($query, array $input = [], $relationsEnabled = true)
    {
        parent::__construct($query, $input, $relationsEnabled);

        $this->enumColumns = collect($this->query->getModel()->getCasts())
            ->filter(fn (string $cast) => Str::startsWith($cast, 'App\Enums'))
            ->keys()
            ->toArray();
    }

    /**
     * Each input key calls the filter method with its name. Only public methods are filters:
     * the protected and private ones are helpers, and calling them with the input would fail.
     *
     * @param  string  $method
     */
    public function methodIsCallable($method): bool
    {
        return parent::methodIsCallable($method)
            && (new ReflectionMethod($this, $method))->isPublic();
    }

    /**
     * Filter methods named after a private column are ignored for everyone but admins.
     */
    protected function setup(): void
    {
        collect($this->privateColumns)
            ->reject(fn (string $column) => $this->canUseColumn($column))
            ->each(fn (string $column) => $this->blacklistMethod(Str::camel($column)));
    }

    /**
     * @return Builder<Model>
     */
    public function handle(): Builder
    {
        $query = parent::handle();

        $this->applyRanges();

        $this->applyNotFilters();

        $this->applyRelations();

        $this->applyConstants();

        $this->applyCounts();

        $this->applySorting();

        $this->applyLimit();

        return $query;
    }

    private function applyRanges(): void
    {
        collect(array_keys($this->input))
            ->filter(fn (string $input) => Str::startsWith($input, [self::MIN_PREFIX, self::MAX_PREFIX]) && ! method_exists($this, Str::camel($input)))
            ->map(fn (string $input) => $this->applyRange($input, $this->input[$input]));
    }

    private function applyNotFilters(): void
    {
        collect(array_keys($this->input))
            ->filter(fn (string $input) => Str::startsWith($input, [self::NOT_PREFIX]) && ! method_exists($this, Str::camel($input)))
            ->map(fn (string $input) => $this->applyNotFilter($input, $this->input[$input]));
    }

    private function applyRelations(): void
    {
        $relations = collect(Arr::wrap($this->input[self::WITH] ?? []))
            ->mapWithKeys(fn ($relation, $key) => is_numeric($key) ? [$key => Str::camel($relation)] : [Str::camel($key) => $relation])
            ->toArray();

        $this->query->with($relations);
    }

    private function applyConstants(): void
    {
        loadConstants()
            ->map(fn (string $constantClass) => Str::snake(class_basename($constantClass)))
            ->filter(fn (string $constantName) => $this->shouldFilterConstant($constantName))
            ->each(function (string $constantName) {
                $value = $this->input[$constantName];
                $column = Str::singular($constantName);
                if (is_array($value)) {
                    $this->query->whereIn($column, $value);
                } else {
                    $this->query->where($column, $value);
                }
            });
    }

    private function applyCounts(): void
    {
        $input = collect(Arr::wrap($this->input[self::COUNT] ?? []))
            ->map(fn ($relation) => Str::camel($relation));

        $rootCounts = $input
            ->filter(fn ($relation) => ! str_contains($relation, '.'))
            ->toArray();

        $nestedCounts = $input
            ->filter(fn ($relation) => str_contains($relation, '.'))
            ->mapWithKeys(function ($relation) {
                $parts = explode('.', $relation);

                return [$parts[0] => fn ($relation) => $relation->withCount($parts[count($parts) - 1])];
            })
            ->toArray();

        if (count($nestedCounts)) {
            $this->query->with($nestedCounts);
        }

        $this->query->withCount($rootCounts);
    }

    private function applyRange(string $input, mixed $value): void
    {
        // Clean up prefixes to determine the table's column name
        $column = str_replace(self::MIN_PREFIX, '', str_replace(self::MAX_PREFIX, '', $input));

        if (! $this->canUseColumn($column)) {
            return;
        }

        $operator = Str::startsWith($input, self::MIN_PREFIX)
            ? '>='
            : '<=';

        if (isDateString($value)) {
            try {
                $date = Carbon::parse($value);
                $dateString = $operator === '>='
                    ? $date->startOfDay()->toDateTimeString()
                    : $date->endOfDay()->toDateTimeString();

                $this->query->where($column, $operator, $dateString);
            } catch (Exception) {
                // A date that cannot be read leaves the range unfiltered
            }
        } else {
            $this->query->where($column, $operator, (float) $value);
        }
    }

    private function applyNotFilter(string $input, mixed $value): void
    {
        // Clean up prefixes to determine the table's column name
        $column = str_replace(self::NOT_PREFIX, '', $input);

        if (! $this->canUseColumn($column)) {
            return;
        }

        if (is_array($value)) {
            $this->whereIn($column, $value, 'and', true); // Where not in
        } else {
            $this->where($column, '!=', $value);
        }
    }

    /**
     * Orders by a 'sort' string if it's present in request
     * This sort string must be with the format 'attribute|orderDirection'
     * for example: a string 'name|asc' means: ordering by 'name' in 'ascending' order.
     * By default, it will order by created_at
     */
    private function applySorting(): void
    {
        $table = $this->query->getModel()->getTable();
        $sortString = $this->input[self::SORT] ?? '';
        [$attribute, $direction] = str_contains($sortString, '|') ? explode('|', $sortString) : [null, null];

        if ($this->input[self::RANDOMIZE] ?? false) {
            $this->query->inRandomOrder();
        } elseif ($attribute && $this->canUseColumn($attribute)) {
            $this->query->getQuery()->orders = []; // clean up any previous ordering

            $this->query->orderBy("$table.$attribute", Str::lower($direction ?? '') === 'desc' ? 'desc' : 'asc');
        } else {
            $this->query->latest("$table.created_at"); // default ordering
        }
    }

    /**
     * Apply limit
     */
    private function applyLimit(): void
    {
        $limit = $this->input[self::LIMIT] ?? null;

        if ($limit) {
            $this->query->limit($limit);
        }
    }

    public function createdBy(mixed $userIds): void
    {
        if ($this->query->getModel() instanceof HasCreatedByInterface) {
            $this->whereIn('created_by', $userIds);
        }
    }

    public function ids(mixed $ids): void
    {
        $this->filterMultiple('id', $ids);
    }

    public function exceptIds(mixed $ids): void
    {
        $this->whereIn('id', $ids, 'and', true);
    }

    public function id(mixed $id): void
    {
        $this->where('id', $id);
    }

    public function modelType(mixed $modelType): void
    {
        $model = $this->query->getModel();

        if (method_exists($model, 'getMorphColumn')) {
            $column = $model->getMorphColumn();
            $this->where("{$column}_type", classByString($modelType));
        }
    }

    public function model(mixed $modelId): void
    {
        $model = $this->query->getModel();

        if ($model instanceof FilterableMorph) {
            $column = $model->getMorphColumn();
            $this->where("{$column}_id", $modelId);
        }
    }

    protected function filterMultiple(string $column, mixed $values): void
    {
        if (is_string($values) && str_contains($values, ',')) {
            $values = explode(',', $values);
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        $this->whereIn($column, $values);
    }

    protected function whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false): void
    {
        if (empty($values)) {
            return;
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        $values = collect($values)
            ->filter() // TODO: filter 'null' strings
            ->toArray();

        $table = $this->query->getModel()->getTable();

        if (count($values)) {
            parent::whereIn("$table.$column", $values, $boolean, $not);
        }
    }

    public function name(mixed $name): void
    {
        collect(Arr::wrap($name))
            ->each(fn ($value) => $this->where('name', 'like', "%$value%"));
    }

    protected function nullable(string $column, mixed $value): void
    {
        $value
            ? $this->whereNotNull($column)
            : $this->whereNull($column);
    }

    /**
     * @param  Builder<Model>|null  $customBuilder
     */
    protected function boolean(string $column, mixed $value, ?Builder $customBuilder = null): void
    {
        $builder = $customBuilder ?? $this;

        $value
            ? $builder->where(fn (Builder $q) => $q->where($column, '!=', 0)->orWhere($column, '!=', false))
            : $builder->where(fn (Builder $q) => $q->where($column, 0)->orWhere($column, false)->orWhereNull($column));
    }

    private function shouldFilterConstant(string $constant): bool
    {
        return ! method_exists($this, $constant)
            && isset($this->input[$constant])
            && in_array(Str::singular($constant), $this->enumColumns)
            && $this->canUseColumn(Str::singular($constant));
    }

    private function canUseColumn(string $column): bool
    {
        return ! in_array($column, $this->privateColumns, true) || hasRole(Roles::ADMIN);
    }
}
