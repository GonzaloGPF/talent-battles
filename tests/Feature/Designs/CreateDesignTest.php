<?php

namespace Tests\Feature\Designs;

use App\Enums\ActivityTypes;
use App\Enums\BattleTypes;
use App\Enums\DesignTypes;
use App\Enums\FileTypes;
use App\Jobs\CreateWatermark;
use App\Jobs\HandleActivity;
use App\Models\Battle;
use App\Models\Design;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDesignTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Queue::fake();
        User::flushEventListeners();
        $this->user = $this->signIn();
    }

    #[Test]
    public function it_let_users_create_a_design_with_a_file()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $design = Design::first();

        $this->assertEquals('Some Name', $design->name);
        $this->assertEquals(DesignTypes::LOGO, $design->design_type);
        $this->assertEquals('Some Description', $design->description);

        tap($design->image, function (File $file) {
            $this->assertEquals(FileTypes::IMAGE, $file->file_type);
            $this->assertEquals('my_image.webp', $file->fullName());
            $this->assertEquals('image/webp', $file->mime);
        });

        Queue::assertPushed(HandleActivity::class, function (HandleActivity $job) {
            return $job->activityType === ActivityTypes::NEW_DESIGN;
        });
    }

    #[Test]
    public function when_creating_a_design_it_creates_a_product_if_user_specifies_sell_option()
    {
        $this->makeRequest([
            'sell' => true,
            'price' => 100,
            'exclusive' => true,
            'file_to_sell' => UploadedFile::fake()->image('sale.webp'),
        ])->assertSuccessful();

        $design = Design::first();

        $this->assertNotNull($design->product);
    }

    #[Test]
    public function when_creating_a_design_it_can_generate_a_tagged_file_if_auto_tag_is_checked()
    {
        $this->makeRequest([
            'sell' => true,
            'price' => 100,
            'auto_tag' => true,
        ])->assertSuccessful();

        $design = Design::first();

        $this->assertNotNull($design->product->file);
        Queue::assertPushed(CreateWatermark::class, function (CreateWatermark $job) {
            return ! is_null($job->delay);
        });
    }

    #[Test]
    public function when_creating_a_design_it_wont_let_sell_if_no_price_is_specified()
    {
        $this->makeRequest([
            'sell' => true,
            'file_to_sell' => UploadedFile::fake()->create('design.mp3'),
        ])->assertUnprocessable();
    }

    #[Test]
    public function when_creating_a_design_it_can_specify_tags()
    {
        $this->makeRequest(['tags' => ['some', 'tag', 'test']])
            ->assertSuccessful();

        $design = Design::first();
        $this->assertCount(3, $design->tags);
    }

    #[Test]
    public function when_creating_a_design_it_adds_points_to_the_user()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->forceFill([
            'points' => 0,
        ])->saveQuietly();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals(config('company.points.awards.upload_creation'), $user->fresh()->points);
    }

    #[Test]
    public function non_authenticated_user_cant_create_designs()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_create_more_than_three_designs_if_account_is_not_verified()
    {
        $this->user
            ->forceFill(['verified_at' => null])
            ->saveQuietly();

        create(Design::class, ['created_by' => $this->user->id], 3);

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_let_create_more_than_three_designs_if_account_is_verified()
    {
        $this->user
            ->forceFill(['verified_at' => now()])
            ->saveQuietly();

        create(Design::class, ['created_by' => $this->user->id], 3);

        $this->makeRequest()
            ->assertSuccessful();
    }

    #[Test]
    public function when_creating_a_design_user_can_create_a_battle_too()
    {
        $this->user
            ->forceFill(['points' => config('company.points.prices.create_battle')])
            ->saveQuietly();

        $this->makeRequest(['battle' => true])
            ->assertSuccessful();

        $battle = Battle::first();

        $this->assertEquals($this->user->id, $battle->created_by);
        $this->assertEquals(BattleTypes::DESIGN, $battle->battle_type);
        $this->assertEquals(2, $this->user->fresh()->points);
    }

    #[Test]
    public function when_creating_a_design_user_it_wont_let_create_a_battle_if_user_does_not_have_enough_points()
    {
        $this->user
            ->forceFill(['points' => config('company.points.prices.create_battle') - 1])
            ->saveQuietly();

        $this->makeRequest(['battle' => true])
            ->assertUnprocessable();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('designs.store'), array_merge([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
            'bpm' => 100,
            'description' => 'Some Description',
            'image' => UploadedFile::fake()->image('my_image.webp'),
        ], $attributes));
    }
}
