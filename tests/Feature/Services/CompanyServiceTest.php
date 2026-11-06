<?php

namespace Tests\Feature\Services;

use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(CompanyService::class);
    }

    #[Test]
    public function it_can_return_company_data()
    {
        $data = $this->service->getData();

        $this->assertNotNull($data);
    }
}
