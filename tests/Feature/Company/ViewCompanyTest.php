<?php

namespace Tests\Feature\Company;

use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewCompanyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_let_fetch_company_data()
    {
        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $companyService = resolve(CompanyService::class);
        $servicesData = $companyService->getServices();
        $buyData = $companyService->getBuy();
        $progressData = $companyService->getProgress();
        $getPointsData = $companyService->getPoints();

        $this->assertEquals(array_merge([
            'services' => $servicesData,
            'buy' => $buyData,
            'progress' => $progressData,
            'points' => $getPointsData,
        ], [
            'battle_ttl' => config('company.battle_ttl'),
        ]), $data);
    }

    private function makeRequest(): TestResponse
    {
        return $this->getJson(route('company.index'));
    }
}
