<?php

use App\Enums\Roles;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

function morphedModel(?string $type, ?int $id): ?Model
{
    if (! $type || ! $id) {
        return null;
    }

    return resolve(classByString($type))::findOrFail($id);
}

function getMorphIdAttribute(?string $modelType = null): string
{
    return Str::of($modelType)
        ->classBasename()
        ->snake()
        ->singular()
        ->append('_id')
        ->value();
}

function getMorphTable(?string $modelType = null): string
{
    return Str::of($modelType)
        ->classBasename()
        ->snake()
        ->plural()
        ->value();
}

/**
 * @return string|null string
 */
function classByString(?string $type): ?string
{
    if (! $type) {
        return null;
    }

    if (Str::startsWith($type, 'App\\Models\\')) {
        return $type;
    }

    return Str::of($type)
        ->singular()
        ->studly()
        ->prepend('App\\Models\\')
        ->value();
}

function toSnake(string $className, ?bool $plural = false): string
{
    return Str::of($className)
        ->classBasename()
        ->snake()
        ->when($plural, fn (Stringable $value) => $value->plural())
        ->value();
}

/**
 * @param  null  $count
 * @return Collection|Model|mixed
 */
function make($class, array $parameters = [], $count = null): Collection|Model
{
    /** @var HasFactory $model */
    $model = resolve($class);

    return $model::factory()
        ->count($count)
        ->make($parameters);
}

/**
 * @param  null  $count
 * @return mixed|Collection|Model
 */
function create($class, array $parameters = [], $count = null): Collection|Model
{
    /** @var HasFactory $model */
    $model = resolve($class);

    return $model::factory()
        ->count($count)
        ->create($parameters);
}

function randomConstant(string $enum, bool $multiple = false): string|array
{
    $constants = collect($enum::cases());

    $itemsCount = $multiple
        ? rand(1, $constants->count())
        : 1;

    $result = $constants->random($itemsCount)
        ->map(fn (BackedEnum $backedEnum) => $backedEnum->value);

    if (! $multiple) {
        return $result->first();
    }

    return $result->toArray();
}

/**
 * Helper that returns an ID of a specified Model (by class name).
 * If there are any Model, it will grab one randomly, if not, it will create a new one using its Factory
 * When using make() it won't write in Database, instead, it will return a 1
 */
function associateTo(string $modelClass, array $attributes = [], bool $create = false): int|string
{
    $isMaking = collect(debug_backtrace())
        ->filter(fn ($item) => isset($item['function']) && $item['function'] === 'make')
        ->filter(fn ($item) => isset($item['file']) && Str::endsWith($item['file'], 'globals.php'))
        ->count();

    if ($isMaking) {
        return 1;
    }

    if ($create) {
        return create($modelClass, $attributes)->id;
    }

    /** @var Model $model */
    $model = resolve($modelClass);

    return $model::where($attributes)->inRandomOrder()->first()?->id
        ?? create($modelClass, $attributes)->id; // call to Factory
}

/**
 * Translate a Model
 */
function modelTitle(string $model, ?bool $plural = false): string
{
    $modelName = Str::snake(class_basename($model));

    return trans_choice("models.$modelName", $plural ? 2 : 1);
}

/**
 * Translate a Model
 */
function constantTitle(string $model, string $value, ?bool $plural = false): string
{
    $modelName = Str::singular(Str::snake(class_basename($model)));

    return trans_choice("constants.$modelName.$modelName", $plural ? 2 : 1);
}

/**
 * Translate a Model name, useful for Constants Models.
 */
function tConstValue(string $constant, string|BackedEnum $value): ?string
{
    if ($value instanceof BackedEnum) {
        $value = $value->value;
    }

    $constant = Str::of(class_basename($constant))
        ->snake()
        ->plural()
        ->value();

    return trans("constants.$constant.$value");
}

/**
 * Translate an action for a Model
 */
function tAction(string $action, ?string $model = null, ?bool $female = null, ?bool $plural = false): string
{
    if ($female === null) {
        $translation = trans("actions.$action");
    } else {
        $translation = trans_choice("actions.$action", $female ? 2 : 1);
    }

    if ($model) {
        $modelName = Str::snake(class_basename($model));
        $translation = modelTitle($modelName, $plural).' '.$translation;
    }

    return $translation;
}

/**
 * Translate given attribute name
 */
function ta($attribute): string
{
    if (! Lang::has("validation.attributes.$attribute")) {
        return Str::title($attribute);
    }

    return trans("validation.attributes.$attribute");
}

/**
 * Translate given label
 */
function tl($label): string
{
    return trans("labels.$label");
}

/**
 * Helper function to know if User has a specific Role.
 */
function hasRole(string|BackedEnum $role, bool $allowAdmin = false, ?User $user = null): bool
{
    /** @var ?User $user */
    if (! $user && auth()->hasUser()) {
        $user = auth()->user();
    }

    if (! $user) {
        return false;
    }

    if ($role instanceof BackedEnum) { // same of $role instanceof UnitEnum
        $role = $role->value;
    }

    if ($allowAdmin && $user->role === Roles::ADMIN) {
        return true;
    }

    return $user->role->value === $role;
}

/**
 * Function that converts a numeric value into an exact abbreviation
 */
function shortenNumber(float $value, int $precision = 1): string
{
    if ($value < 900) {
        // 0 - 900
        $n_format = number_format($value, $precision);
        $suffix = '';
    } elseif ($value < 900000) {
        // 0.9k-850k
        $n_format = number_format($value / 1000, $precision);
        $suffix = 'K';
    } elseif ($value < 900000000) {
        // 0.9m-850m
        $n_format = number_format($value / 1000000, $precision);
        $suffix = 'M';
    } elseif ($value < 900000000000) {
        // 0.9b-850b
        $n_format = number_format($value / 1000000000, $precision);
        $suffix = 'B';
    } else {
        // 0.9t+
        $n_format = number_format($value / 1000000000000, $precision);
        $suffix = 'T';
    }
    // Remove unnecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
    // Intentionally does not affect partials, eg "1.50" -> "1.50"
    if ($precision > 0) {
        $dotZero = '.'.str_repeat('0', $precision);
        $n_format = str_replace($dotZero, '', $n_format);
    }

    return $n_format.$suffix;
}

/**
 * Creates a file and returns the path where it has been saved.
 */
function generateTestFile(?string $name = 'Test', ?string $extension = null, string $fileContent = 'This is a test content'): string
{
    $extension = $extension ?? 'txt';

    $path = "$name.$extension";

    Storage::put($path, $fileContent);

    return $path;
}

/**
 * @return mixed|Model|Collection|LegacyMockInterface|MockInterface
 */
function makeMock(string $className, array $attributes = [], int $times = 0)
{
    if (! $times) {
        return Mockery::mock(make($className, $attributes));
    }

    return collect(range(0, $times - 1))
        ->map(fn () => Mockery::mock(make($className, $attributes)));
}

function mockFileContent(string $path): ?string
{
    try {
        return Storage::disk('mocks')->get($path);
    } catch (FileNotFoundException $e) {
        return null;
    }
}

/**
 * Dates always have formats 'Y-m-d' or 'Y/m/d'
 */
function isDateString($value): bool
{
    if (empty($value)) {
        return false;
    }

    $fails = Validator::make([
        'date' => $value,
    ], [
        'date' => 'date',
    ])->fails();

    return ! $fails;
}

/**
 * Returns a Collection of Carbon dates
 */
function createPeriod(?string $minDate = null, ?string $maxDate = null, ?string $interval = '1 month'): Collection
{
    $minDate = Carbon::parse($minDate) ?? today();

    $maxDate = $maxDate ?? Carbon::parse($minDate)->addYear();

    return collect(CarbonPeriod::create($minDate, $interval, $maxDate));
}

/**
 * Remove special chars in string.
 */
function removeSpecialChars($string, bool $removeDots = false): string
{
    //    $string = preg_replace('/[\xE2\x80\xAF]/', '', $string);
    //    $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

    $search = explode(',', 'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,º,ñ');
    $replace = explode(',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,o,n');

    $withoutAccents = str_replace($search, $replace, $string);

    if ($removeDots) {
        return str_replace('.', '', $withoutAccents);
    }

    return $withoutAccents;
}

function loadModels(string $interface): Collection
{
    return collect(glob(app_path('Models').'/*.php'))
        ->each(fn (string $file) => require_once $file)
        ->map(fn (string $filePath) => 'App\\Models\\'.basename($filePath, '.php'))
        ->filter(fn (string $class) => implementsInterface($class, $interface))
        ->map(fn (string $modelString) => resolve($modelString));
}

function loadConstants(): Collection
{
    return collect(glob(app_path('Enums').'/*.php'))
        ->each(fn (string $file) => require_once $file)
        ->map(fn (string $filePath) => 'App\\Enums\\'.basename($filePath, '.php'))
        ->filter(fn (string $class) => implementsInterface($class, UnitEnum::class));
}

function implementsInterface(string $class, string $interface): bool
{
    try {
        return (new ReflectionClass($class))->implementsInterface($interface);
    } catch (ReflectionException $e) {
        return false;
    }
}

/**
 * Formats a currency value
 */
function money($value, ?string $currencySymbolOrHtmlEntity = '€'): string
{
    error_reporting(E_ALL ^ E_DEPRECATED); // php 7.4 problem https://github.com/dompdf/dompdf/issues/2003
    setlocale(LC_MONETARY, 'es_ES.UTF-8');

    //    return money_format('%.2n', (float)round($value, 2));
    $value = round(round(round($value, 4), 3), 2);

    return number_format($value, 2, ',', '.').' '.$currencySymbolOrHtmlEntity;
}

/**
 * Debugging purposes
 */
function debugBackTrace(): void
{
    print_r(debug_backtrace(2), true);
}
