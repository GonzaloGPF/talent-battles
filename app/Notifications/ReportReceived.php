<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use App\Interfaces\NotyInterface;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReceived extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Report $report)
    {
        $this->helper = resolve(NotificationHelper::class);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return $this->helper->getVia(self::class, $notifiable);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return $this->helper->toMail($this);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->helper->toArray($this);
    }

    public function getMessageKeys(): array
    {
        return [
            'reported' => $this->report->reportable?->name,
        ];
    }

    public function getLevel(): ?string
    {
        return NotificationHelper::WARNING;
    }

    public function getIcon(): ?string
    {
        return 'mdi-alert';
    }

    public function getTarget(): Model
    {
        return $this->report->reportable;
    }
}
