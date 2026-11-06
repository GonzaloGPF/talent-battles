<?php

namespace Tests\Feature\Designs;

use App\Enums\BattleStates;
use App\Enums\DesignTypes;
use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Models\Battle;
use App\Models\Design;
use App\Models\File;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateDesignTest extends TestCase
{
    use RefreshDatabase;

    private Design $design;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->user = $this->signIn();
        $this->design = create(Design::class, [
            'created_by' => $this->user->id,
            'design_type' => DesignTypes::LOGO,
        ]);
        create(File::class, [
            'created_by' => $this->user->id,
            'fileable_id' => $this->design->id,
            'fileable_type' => Design::class,
            'file_type' => FileTypes::IMAGE,
            'mime' => 'image/jpeg',
            'path' => generateTestFile('test', 'jpg'),
            'name' => 'test',
        ]);
    }

    #[Test]
    public function it_let_users_update_its_designs()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals('Edited', $this->design->fresh()->name);
    }

    #[Test]
    public function when_updating_a_design_it_let_modify_file()
    {
        $this->makeRequest([
            'image' => UploadedFile::fake()->image('updated.webp'),
        ])->assertSuccessful();

        $this->assertEquals('updated.webp', $this->design->image->fullName());
    }

    #[Test]
    public function it_wont_modify_file_if_it_is_in_sale()
    {
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $this->design->id,
        ]);

        $this->makeRequest([
            'image' => UploadedFile::fake()->image('updated.webp'),
        ])->assertSuccessful();

        $this->assertNotEquals('updated.webp', $this->design->image->fullName());
    }

    #[Test]
    public function when_modifying_a_design_it_can_modify_tags()
    {
        $this->design->forceFill([
            'tags' => null,
        ])->saveQuietly();

        $this->makeRequest([
            'tags' => ['test_1', 'test_2'],
        ])->assertSuccessful();

        $this->assertCount(2, $this->design->fresh()->tags);
    }

    #[Test]
    public function it_wont_let_update_designs_from_other_user()
    {
        $otherUser = create(User::class);
        $this->design->created_by = $otherUser->id;
        $this->design->save();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_update_designs_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_update_designs_if_its_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        $this->design->battles()->save($battle);

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(route('designs.update', $this->design), array_merge(['name' => 'edited'], $attributes));
    }
}
