<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use App\Interfaces\HasCreatedByInterface;
use App\Interfaces\NotyInterface;
use App\Models\Battle;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BattleStarted extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Battle $battle)
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
            'creation' => $this->battle->getCreations()
                ->filter(fn (HasCreatedByInterface $creation) => $creation->created_by === auth()->id())
                ->first()
                ?->name,
        ];
    }

    public function getLevel(): ?string
    {
        return NotificationHelper::INFO;
    }

    public function getIcon(): ?string
    {
        return 'mdi-sword-cross';
    }

    public function getTarget(): Model
    {
        return $this->battle;
    }

    public function getFrontUrl(): string
    {
        return url('admin/battles', ['in_progress' => true]);
    }
}
