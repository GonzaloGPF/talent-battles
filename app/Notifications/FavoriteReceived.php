<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use App\Interfaces\NotyInterface;
use App\Models\Favorite;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FavoriteReceived extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Favorite $favorite)
    {
        $this->helper = resolve(NotificationHelper::class);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->helper->toArray($this);
    }

    public function getMessageKeys(): array
    {
        return [
            'name' => $this->favorite->createdBy?->getNickName(),
            'target' => $this->favorite->favoritable?->getAttribute('name'),
        ];
    }

    public function getLevel(): ?string
    {
        return NotificationHelper::INFO;
    }

    public function getIcon(): ?string
    {
        return 'mdi-heart';
    }

    public function getTarget(): Model
    {
        return $this->favorite->favoritable;
    }
}
