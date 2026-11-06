<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use App\Interfaces\NotyInterface;
use App\Models\Battle;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BattleLost extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Battle $battle, public User $loserUser)
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

    /**
     * @return null[]
     */
    public function getMessageKeys(): array
    {
        return [
            'creation' => $this->battle->getCreations()
                ->firstWhere('created_by', $this->loserUser->id)
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
        $userId = $this->loserUser->id;

        return url("admin/battles?lost_by[0]=$userId");
    }
}
