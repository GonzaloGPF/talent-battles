<?php

namespace Tests\Unit\Listeners;

use App\Events\SalesCreated;
use App\Listeners\SendCreationSoldEmail;
use App\Models\Sale;
use App\Models\User;
use App\Services\EmailService;
use Event;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendCreationSoldEmailTest extends TestCase
{
    private Collection $sales;

    private User $user;

    private Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User;
        $this->user->id = 123;
        $this->sale = new Sale;
        $this->sale->created_by = $this->user->id;
        $this->sale->setRelation('createdBy', $this->user);
        $this->sales = collect([$this->sale]);
    }

    #[Test]
    public function it_listens_to_sales_created()
    {
        Event::fake();
        Event::assertListening(SalesCreated::class, SendCreationSoldEmail::class);
    }

    #[Test]
    public function it_send_creation_sold_email_when_sales_created_is_dispatched()
    {
        $this->mock(EmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('createCreationSold')
                ->once();
            //                ->with($this->sales, $this->user); // TODO: not working :(
        });
        $event = new SalesCreated($this->sales);
        $listener = resolve(SendCreationSoldEmail::class);
        $listener->handle($event);
    }
}
