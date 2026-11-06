<?php

namespace App\Services;

use App\Facades\Logger;
use App\Models\Donation;
use Exception;

class DonationService
{
    public function create(float $amount, ?string $message = null, ?int $createdBy = null): ?Donation
    {
        try {
            return Donation::forceCreate([
                'message' => $message,
                'amount' => $amount,
                'created_by' => $createdBy,
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }
}
