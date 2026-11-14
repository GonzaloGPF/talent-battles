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
 * Tells admins that someone holding a claim link deleted the profile. The account is anonymized by then, so the name
 * it had comes apart.
 */
class ProfileDeleted extends Notification implements NotyInterface
{
    use Queueable;

    private NotificationHelper $helper;

    public function __construct(public User $user, public string $name)
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
     * @return array{name: string}
     */
    public function getMessageKeys(): array
    {
        return ['name' => $this->name];
    }

    public function getLevel(): ?string
    {
        return NotificationHelper::WARNING;
    }

    public function getIcon(): ?string
    {
        return 'mdi-account-remove';
    }

    public function getTarget(): Model
    {
        return $this->user;
    }

    public function getFrontUrl(): string
    {
        return url('admin/users');
    }
}
