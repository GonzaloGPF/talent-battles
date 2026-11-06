<?php

namespace Tests\Feature\Files;

use App\Models\File;
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

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('files.store'), array_merge([
            'model_type' => 'user',
            'model_id' => $this->model->id,
            'files' => [UploadedFile::fake()->image('test.webp')],
        ], $attributes));
    }
}
