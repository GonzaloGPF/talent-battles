<?php

namespace Tests\Feature\Files;

use App\Enums\Roles;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use RefreshDatabase;

    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->file = create(File::class, ['path' => generateTestFile('test')]);
    }

    #[Test]
    public function authenticated_user_can_delete_an_file()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertDatabaseMissing('files', ['id' => $this->file->id]);
    }

    #[Test]
    public function it_wont_let_deleting_a_file_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $otherUser->files()->save($this->file);

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(): TestResponse
    {
        return $this->deleteJson(route('files.destroy', $this->file));
    }
}
