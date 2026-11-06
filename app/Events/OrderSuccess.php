<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Order;
use App\Notifications\OrderCompleted;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderSuccess implements NotyEventInterface, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Order $order)
    {
        app()->setLocale($order->createdBy?->locale);
    }

    public function notify(): void
    {
        $this->order->createdBy?->notify(new OrderCompleted($this->order));
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->order->createdBy?->getChannelName());
    }
}
