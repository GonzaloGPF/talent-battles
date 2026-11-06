<?php

namespace Tests\Feature\Files;

use App\Enums\FileTypes;
use App\Enums\Roles;
use App\Jobs\ProcessImageJob;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateFileTest extends TestCase
{
    use RefreshDatabase;

    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->file = make(File::class, [
            'fileable_type' => User::class,
            'fileable_id' => create(User::class)->id,
        ]);
        $this->file->saveQuietly();
    }

    #[Test]
    public function authenticated_user_can_update_an_specific_file()
    {
        Queue::fake();
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertEquals('edited', $this->file->fresh()->name);

        Queue::assertPushed(ProcessImageJob::class);
    }

    #[Test]
    public function it_wont_let_update_a_file_from_other_user()
    {
        /** @var User $otherUser */
        $otherUser = create(User::class);
        $this->file->forceFill([
            'fileable_id' => $otherUser->id,
        ])->saveQuietly();

        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->putJson(
            route('files.update', $this->file),
            array_merge([
                'file_type' => FileTypes::IMAGE,
                'files' => [
                    UploadedFile::fake()->image('edited.webp'),
                ],
            ], $attributes)
        );
    }
}
