<?php

namespace App\Notifications;

use App\Helpers\NotificationHelper;
use App\Interfaces\NotyInterface;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells admins that someone holding a claim link kept the profile, so they can check it was its owner.
 */
class ProfileClaimed extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    public function __construct(public User $user)
    {
        $this->helper = resolve(NotificationHelper::class);
    }

    /**
     * Admins always get it by email too, whatever their settings.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->helper->toMail($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->helper->toArray($this);
    }

    /**
     * @return array{name: string, email: string}
     */
    public function getMessageKeys(): array
    {
        return [
            'name' => $this->user->getNickName(),
            'email' => $this->user->email,
        ];
    }

    public function getLevel(): ?string
    {
        return NotificationHelper::SUCCESS;
    }

    public function getIcon(): ?string
    {
        return 'mdi-account-check';
    }

    public function getTarget(): Model
    {
        return $this->user;
    }
}
