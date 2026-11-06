<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        $this->withoutVite();
    }

    /**
     * Helper for signing in a user. If no user is provided it will log in an Admin user.
     *
     * @param  null  $user
     */
    protected function signIn($user = null, ?string $guard = null): User
    {
        /** @var User $user */
        $user = $user ?: User::factory()->admin()->create();

        $this->actingAs($user, $guard);

        return $user;
    }

    protected function logout(?string $guard = null): void
    {
        User::flushEventListeners();
        auth($guard)->logout();
    }

    protected function assertBelongsTo(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? Str::camel(class_basename($relatedModel));

        $this->assertInstanceOf(BelongsTo::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertHasMany(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel, true);

        $this->assertInstanceOf(HasMany::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertHasManyThrough(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel, true);

        $this->assertInstanceOf(HasManyThrough::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertHasOne(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel);

        $this->assertInstanceOf(HasOne::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertBelongsToMany(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel, true);

        $this->assertInstanceOf(BelongsToMany::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertMorphsMany(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel, true);

        $this->assertInstanceOf(MorphMany::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertMorphsOne(Model $model, string $relatedModel, ?string $method = null)
    {
        $methodName = $method ?? $this->sanitizeRelation($relatedModel);

        $this->assertInstanceOf(MorphOne::class, $model->$methodName());
        $this->assertInstanceOf($relatedModel, $model->$methodName()->getModel());
    }

    protected function assertMorphsTo(Model $model, string $method)
    {
        $this->assertInstanceOf(MorphTo::class, $model->$method());
    }

    protected function assertUsesTrait(Model $model, string $traitClass)
    {
        $this->assertTrue(in_array($traitClass, class_uses($model)));
    }

    protected function assertInArray($value, array $array)
    {
        $this->assertTrue(in_array($value, $array), "Value [$value] not found in array");
    }

    protected function assertNotInArray($value, array $array)
    {
        $this->assertFalse(in_array($value, $array), "Value [$value] found in array");
    }

    protected function assertHasCaster(Model $model, string $casterClass, string $attribute)
    {
        $this->assertEquals($casterClass, $model->getCasts()[$attribute]);
    }

    protected function assertIsDateString($value)
    {
        $this->assertTrue(isDateString($value), "Invalid date $value");
    }

    private function sanitizeRelation(string $relatedModel, ?bool $plural = false): string
    {
        return (string) Str::of($relatedModel)
            ->when($plural, fn (Stringable $s) => $s->plural())
            ->when(! $plural, fn (Stringable $s) => $s->singular())
            ->camel()
            ->classBasename();
    }
}
