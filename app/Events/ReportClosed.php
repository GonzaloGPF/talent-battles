<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Report;
use App\Notifications\ReportSolved;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportClosed implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Report $report)
    {
        //
    }

    public function notify(): void
    {
        $this->report->createdBy?->notify(new ReportSolved($this->report));
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->report->createdBy->getChannelName());
    }
}
