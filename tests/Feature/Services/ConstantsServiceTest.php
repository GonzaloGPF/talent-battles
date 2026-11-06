<?php

namespace Tests\Feature\Services;

use App\Services\ConstantsService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConstantsServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConstantsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = resolve(ConstantsService::class);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function it_can_return_all_constants_data()
    {
        $data = $this->service->getConstants();

        $this->assertNotNull($data);
    }
}
