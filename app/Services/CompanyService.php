<?php

namespace App\Services;

class CompanyService
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $servicesData = $this->getServices();
        $buyData = $this->getBuy();
        $progressData = $this->getProgress();
        $pointsData = $this->getPoints();

        return array_merge([
            'services' => $servicesData,
            'buy' => $buyData,
            'progress' => $progressData,
            'points' => $pointsData,
        ], [
            'battle_ttl' => config('company.battle_ttl'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getServices(): array
    {
        return config('company.services');
    }

    /**
     * @return array<string, mixed>
     */
    public function getBuy(): array
    {
        return config('company.buy');
    }

    /**
     * @return array<string, mixed>
     */
    public function getProgress(): array
    {
        return config('company.progress');
    }

    /**
     * @return array<string, mixed>
     */
    public function getPoints(): array
    {
        return config('company.points');
    }
}
