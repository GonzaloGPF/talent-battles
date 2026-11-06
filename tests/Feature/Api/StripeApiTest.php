<?php

namespace Tests\Feature\Api;

use App\Api\StripeApi;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Tests\TestCase;

class StripeApiTest extends TestCase
{
    use RefreshDatabase;

    private StripeApi $service;

    private User $user;

    private Country $country;

    private Region $region;

    protected function setUp(): void
    {
        parent::setUp();
        $this->country = create(Country::class, [
            'code' => 'ES',
            'name' => 'Spain',
        ]);
        $this->region = create(Region::class, [
            'country_id' => $this->country->id,
            'name' => 'Madrid',
        ]);
        $this->user = create(User::class, [
            'address' => [
                'region_id' => $this->region->id,
                'city' => 'Madrid',
                'postcode' => 28000,
            ],
        ]);
        $this->service = resolve(StripeApi::class);
    }

    /**
     * @throws ApiErrorException
     */
    #[Test]
    public function it_know_how_to_get_or_create_stripe_account()
    {
        $this->markTestIncomplete();
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->service->getOrCreateAccount($this->user);
    }
}
