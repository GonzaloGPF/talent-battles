<?php

namespace Tests\Feature\Users;

use App\Enums\EmailTypes;
use App\Enums\Roles;
use App\Jobs\SendEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        Queue::fake();
        Event::fake();
    }

    #[Test]
    public function administrator_can_create_users()
    {
        $this->makeRequest()
            ->assertSuccessful();

        Queue::assertPushed(SendEmail::class, function (SendEmail $job) {
            return $job->email->email_type === EmailTypes::VERIFY_EMAIL;
        });
    }

    #[Test]
    public function when_creating_a_user_it_can_specify_image()
    {
        $image = UploadedFile::fake()->image('test.webp');

        $response = $this->makeRequest(['image' => $image])
            ->assertSuccessful()
            ->json();

        $user = User::find($response['data']['id']);

        $this->assertEquals('test', $user->image->name);
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('users.store'), array_merge([
            'role' => Roles::USER->value,
            'name' => 'Test',
            'email' => 'test@mail.com',
        ], $attributes));
    }
}
