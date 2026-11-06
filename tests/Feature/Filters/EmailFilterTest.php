<?php

namespace Tests\Feature\Filters;

use App\Enums\EmailTypes;
use App\Models\Email;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        create(Email::class, [], 3);
    }

    #[Test]
    public function it_can_filter_email_by_email_type()
    {
        Email::query()->update(['email_type' => EmailTypes::VERIFY_EMAIL]);

        create(Email::class, ['email_type' => EmailTypes::ORDER_SUCCESS]);

        $data = Email::filter(['email_types' => [EmailTypes::ORDER_SUCCESS->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_email_by_sent()
    {
        Email::query()->update(['sent' => null]);

        create(Email::class, ['sent' => now()]);

        $data = Email::filter(['sent' => true])->get();

        $this->assertCount(1, $data);

        $data = Email::filter(['sent' => false])->get();

        $this->assertCount(3, $data);
    }

    #[Test]
    public function it_can_filter_email_by_to()
    {
        create(Email::class, ['to' => ['test@mail.com']]);

        $data = Email::filter(['to' => 'test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_email_by_cc()
    {
        create(Email::class, ['cc' => ['test@mail.com']]);

        $data = Email::filter(['cc' => 'test'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_email_by_bcc()
    {
        create(Email::class, ['bcc' => ['test@mail.com']]);

        $data = Email::filter(['bcc' => 'test'])->get();

        $this->assertCount(1, $data);
    }
}
