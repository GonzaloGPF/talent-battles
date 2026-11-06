<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Design;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Track;
use App\Notifications\DesignSold;
use App\Notifications\TrackSold;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SalesCreated implements NotyEventInterface, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Collection $sales) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return $this->sales
            ->filter(fn (Sale $sale) => $sale->created_by)
            ->map(fn (Sale $sale) => new PrivateChannel($sale->createdBy?->getChannelName()))
            ->toArray();
    }

    public function notify(): void
    {
        $this->sales
            ->map(fn (Sale $sale) => $sale->product)
            ->filter()
            ->each(function (Product $product) {
                $orderable = $product->orderable;

                if ($orderable instanceof Track) {
                    $orderable->createdBy?->notify(new TrackSold($orderable));
                }

                if ($orderable instanceof Design) {
                    $orderable->createdBy?->notify(new DesignSold($orderable));
                }
            });
    }
}
