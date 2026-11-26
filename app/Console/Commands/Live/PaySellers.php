<?php

namespace App\Console\Commands\Live;

use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class PaySellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pay-sellers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer to their sellers the sales that could not be paid, for example because their Stripe account was not ready';

    /**
     * Sales from before transfers have no order line, so they are left out.
     */
    public function handle(PaymentService $paymentService): int
    {
        $sales = Sale::withoutGlobalScopes()
            ->whereNull('transfer_id')
            ->whereNotNull('order_line_id')
            ->with('createdBy')
            ->get();

        $paid = $sales->filter(fn (Sale $sale) => $paymentService->payToSeller($sale))->count();

        $this->info("Paid $paid of {$sales->count()} pending sales.");

        return $paid === $sales->count() ? self::SUCCESS : self::FAILURE;
    }
}
