<?php

namespace Tests\Feature\Designs;

use App\Enums\BattleStates;
use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Models\Battle;
use App\Models\Design;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteDesignTest extends TestCase
{
    use RefreshDatabase;

    private Design $design;

    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->design = create(Design::class);
        $this->file = make(File::class, [
            'fileable_id' => $this->design,
            'fileable_type' => Design::class,
            'file_type' => FileTypes::AUDIO,
            'mime' => 'audio/mpeg',
            'path' => generateTestFile('test', 'mp3'),
            'name' => 'test',
        ]);
        $this->file->saveQuietly();
    }

    #[Test]
    public function it_let_users_delete_its_design()
    {
        $this->assertTrue(Storage::exists($this->file->path));

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('designs', ['id' => $this->design->id]);
        $this->assertFalse(Storage::exists($this->file->path));
    }

    #[Test]
    public function it_wont_let_delete_designs_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->design->created_by = $otherUser->id;
        $this->design->save();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    #[Test]
    public function it_wont_let_delete_designs_to_non_authenticated_users()
    {
        $this->logout();

        $this->makeRequest()
            ->assertUnauthorized();
    }

    #[Test]
    public function it_wont_let_delete_designs_if_its_in_battle()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        $this->design->battles()->save($battle);

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('designs.destroy', $this->design));
    }
}
