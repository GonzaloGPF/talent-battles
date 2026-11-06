<?php

namespace Tests\Feature\Services;

use App\Enums\EmailTypes;
use App\Mail\ContactEmail;
use App\Mail\CreationSoldEmail;
use App\Mail\OrderSuccessEmail;
use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\Email;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Sale;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(EmailService::class);
    }

    #[Test]
    public function it_can_create_an_order_success_email()
    {
        /** @var Order $order */
        $order = create(Order::class);
        /** @var OrderLine $orderLine */
        $orderLine = create(OrderLine::class, ['order_id' => $order->id]);

        $email = $this->service->createOrderSuccess($order);
        $this->assertNull($email->created_by);
        $this->assertEquals(EmailTypes::ORDER_SUCCESS, $email->email_type);
        $this->assertEquals($order->id, $email['meta']['order_id']);
        $this->assertEquals($order->createdBy->getNickName(), $email['meta']['user_name']);
        $this->assertEquals($orderLine->description, $email['meta']['lines'][0]['description']);
    }

    #[Test]
    public function it_can_create_a_verify_email()
    {
        /** @var User $user */
        $user = create(User::class, ['token' => 'SomeToken']);

        $this->signIn($user);

        $email = $this->service->createVerifyEmail($user);
        $this->assertEquals($user->id, $email->created_by);
        $this->assertEquals(EmailTypes::VERIFY_EMAIL, $email->email_type);
        $this->assertEquals($user->getNickName(), $email['meta']['user_name']);
        $this->assertEquals($user->email, $email['meta']['email']);
        $this->assertStringContainsString('users/SomeToken/verify', $email['meta']['url']);
    }

    #[Test]
    public function it_can_create_a_password_reset()
    {
        /** @var User $user */
        $user = create(User::class, ['token' => 'SomeToken']);

        $email = $this->service->createPasswordReset($user);
        $this->assertEquals(EmailTypes::RESET_PASSWORD, $email->email_type);
        $this->assertEquals($user->getNickName(), $email['meta']['user_name']);
        $this->assertEquals($user->email, $email['meta']['email']);
        $this->assertStringContainsString('password/reset/SomeToken', $email['meta']['url']);
    }

    #[Test]
    public function it_can_create_a_password_reset_of_a_new_user()
    {
        /** @var User $user */
        $user = create(User::class, ['token' => 'SomeToken']);

        $email = $this->service->createPasswordReset($user, true);
        $this->assertEquals(EmailTypes::RESET_PASSWORD, $email->email_type);
        $this->assertEquals($user->getNickName(), $email['meta']['user_name']);
        $this->assertEquals($user->email, $email['meta']['email']);
        $this->assertTrue($email['meta']['is_new']);
        $this->assertStringContainsString('password/reset/SomeToken', $email['meta']['url']);
    }

    #[Test]
    public function it_can_create_creation_sold()
    {
        /** @var User $user */
        $user = create(User::class, ['token' => 'SomeToken']);
        /** @var Sale $sale */
        $sale = create(Sale::class);

        $sales = collect([$sale]);

        $email = $this->service->createCreationSold($sales, $user);
        $this->assertEquals(EmailTypes::CREATION_SOLD, $email->email_type);
        $this->assertEquals($user->getNickName(), $email['meta']['user_name']);
        $this->assertEquals($sale->description, $email['meta']['lines'][0]['description']);
        $this->assertEquals($sale->amount, $email['meta']['lines'][0]['amount']);
        $this->assertEquals($sale->commission_amount, $email['meta']['lines'][0]['commission_amount']);
        $this->assertEquals($sale->total, $email['meta']['lines'][0]['total']);
    }

    #[Test]
    public function it_can_create_contact_email()
    {
        $email = $this->service->createContactEmail('some@mail.com', 'some message', ['admin@mail.com']);

        $this->assertEquals(EmailTypes::CONTACT_EMAIL, $email->email_type);
        $this->assertEquals('some@mail.com', $email['meta']['email']);
        $this->assertEquals('some message', $email['meta']['message']);
        $this->assertInArray('admin@mail.com', $email['to']);
    }

    #[Test]
    public function it_can_create_invitation_email()
    {
        /** @var User $creator */
        $creator = create(User::class, ['name' => 'someCreatorName']);

        /** @var Invitation $invitation */
        $invitation = create(Invitation::class, [
            'created_by' => $creator->id,
            'email' => 'some@mail.com',
            'name' => 'someUserName',
        ]);

        $email = $this->service->createInvitationEmail($invitation);

        $this->assertEquals(EmailTypes::INVITATION_EMAIL, $email->email_type);
        $this->assertEquals($invitation->id, $email['meta']['invitation_id']);
    }

    #[Test]
    public function it_can_return_order_success_mailable()
    {
        $email = create(Email::class, [
            'email_type' => EmailTypes::ORDER_SUCCESS,
            'meta' => [
                'user_name' => 'SomeName',
                'lines' => [],
            ],
        ]);

        $mailable = $this->service->getEmail($email);

        $this->assertInstanceOf(OrderSuccessEmail::class, $mailable);
    }

    #[Test]
    public function it_can_return_creation_sold_mailable()
    {
        $email = create(Email::class, [
            'email_type' => EmailTypes::CREATION_SOLD,
            'meta' => [
                'user_name' => 'SomeName',
                'lines' => [],
            ],
        ]);

        $mailable = $this->service->getEmail($email);

        $this->assertInstanceOf(CreationSoldEmail::class, $mailable);
    }

    #[Test]
    public function it_can_return_contact_email_mailable()
    {
        $email = create(Email::class, [
            'email_type' => EmailTypes::CONTACT_EMAIL,
            'meta' => [
                'email' => 'SomeName',
                'message' => 'SomeMessage',
            ],
        ]);

        $mailable = $this->service->getEmail($email);

        $this->assertInstanceOf(ContactEmail::class, $mailable);
    }

    #[Test]
    public function it_can_return_verify_email_mailable()
    {
        $email = create(Email::class, [
            'email_type' => EmailTypes::VERIFY_EMAIL,
            'meta' => [
                'user_name' => 'SomeName',
                'email' => 'SomeEmail',
                'url' => 'SomeUrl',
            ],
        ]);

        $mailable = $this->service->getEmail($email);

        $this->assertInstanceOf(VerifyEmail::class, $mailable);
    }

    #[Test]
    public function it_can_return_password_reset_mailable()
    {
        $email = create(Email::class, [
            'email_type' => EmailTypes::RESET_PASSWORD,
            'meta' => [
                'user_name' => 'SomeName',
                'url' => 'SomeUrl',
            ],
        ]);

        $mailable = $this->service->getEmail($email);

        $this->assertInstanceOf(ResetPasswordEmail::class, $mailable);
    }
}
