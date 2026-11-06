<?php

namespace App\Services;

class CompanyService
{
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

    public function getServices(): array
    {
        return config('company.services');
    }

    public function getBuy(): array
    {
        return config('company.buy');
    }

    public function getProgress(): array
    {
        return config('company.progress');
    }

    public function getPoints(): array
    {
        return config('company.points');
    }
}
