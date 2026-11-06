<?php

namespace App\Events;

use App\Interfaces\NotyEventInterface;
use App\Models\Report;
use App\Notifications\ReportReceived;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCreated implements NotyEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Report $report) {}

    public function notify(): void
    {
        $this->report->getReportedUser()?->notify(new ReportReceived($this->report));
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->report->getReportedUser()?->getChannelName());
    }
}
