<?php

namespace App\Http\Controllers;

use App\Http\Resources\DonationResource;
use App\Models\Donation;
use App\Services\DonationService;
use Illuminate\Http\JsonResponse;

class DonationController extends Controller
{
    public function __construct(public DonationService $donationService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Donation.
     */
    public function index(): JsonResponse
    {
        $donations = Donation::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(DonationResource::collection($donations));
    }
}
