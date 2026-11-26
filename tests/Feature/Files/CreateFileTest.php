<?php

namespace Tests\Feature\Files;

use App\Enums\Roles;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateFileTest extends TestCase
{
    use RefreshDatabase;

    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        File::flushEventListeners();
        $this->signIn();
        $this->model = create(User::class);
    }

    #[Test]
    public function authenticated_user_can_create_a_file_for_a_model()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->model->files);
    }

    #[Test]
    public function when_creating_a_file_it_can_send_a_single_file_input()
    {
        $this->makeRequest([
            'files' => null,
            'file' => UploadedFile::fake()->image('test.webp'),
        ])->assertStatus(Response::HTTP_CREATED);

        $this->assertCount(1, $this->model->files);
    }

    #[Test]
    public function a_guest_cannot_add_files()
    {
        $this->logout();

        $this->makeRequest()
            ->assertForbidden();

        $this->assertCount(0, $this->model->files);
    }

    #[Test]
    public function a_user_cannot_add_files_to_a_model_of_another_user()
    {
        $this->signIn(create(User::class, ['role' => Roles::USER]));

        $this->makeRequest()
            ->assertForbidden();

        $this->assertCount(0, $this->model->files);
    }

    #[Test]
    public function a_user_can_add_files_to_their_own_track()
    {
        $user = $this->signIn(create(User::class, ['role' => Roles::USER]));
        $track = create(Track::class, ['created_by' => $user->id]);

        $this->makeRequest(['model_type' => 'track', 'model_id' => $track->id])
            ->assertCreated();

        $this->assertCount(1, $track->files);
    }

    #[Test]
    public function adding_files_to_a_model_that_does_not_exist_returns_404()
    {
        $this->makeRequest(['model_id' => 999999])
            ->assertNotFound();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('files.store'), array_merge([
            'model_type' => 'user',
            'model_id' => $this->model->id,
            'files' => [UploadedFile::fake()->image('test.webp')],
        ], $attributes));
    }
}
