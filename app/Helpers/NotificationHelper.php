<?php

namespace App\Helpers;

use App\Interfaces\NotyInterface;
use App\Models\Track;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class NotificationHelper
{
    const SUCCESS = 'success';

    const INFO = 'info';

    const WARNING = 'warning';

    const ERROR = 'error';

    public function getVia(string $notificationClassName, object $notifiable): array
    {
        $via = ['database'];

        $notificationName = toSnake($notificationClassName);

        $notificationSetting = data_get($notifiable, "notification_settings.$notificationName");

        if (! empty($notificationSetting['mail'])) {
            $via[] = 'mail';
        }

        if (! empty($notificationSetting['broadcast'])) {
            $via[] = 'broadcast';
        }

        return $via;
    }

    public function toArray(NotyInterface $noty): array
    {
        return [
            'noty_id' => 'noty_'.md5($noty->getTarget()->id.get_class($noty)),
            'message_keys' => $noty->getMessageKeys(),
            'level' => $noty->getLevel(),
            'icon' => $noty->getIcon(),
            'url' => $this->getFrontUrl($noty),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(NotyInterface $noty): MailMessage
    {
        $notificationName = Str::snake(class_basename($noty));

        return (new MailMessage)
            ->greeting(trans("notifications.{$notificationName}_title"))
            ->line(trans("notifications.{$notificationName}_message", $noty->getMessageKeys()))
            ->action(trans('actions.view'), $this->getFrontUrl($noty))
            ->salutation(trans('emails.greetings', ['app' => config('app.name')]));
    }

    private function getFrontUrl(NotyInterface $noty): string
    {
        if (method_exists($noty, 'getFrontUrl')) {
            return $noty->getFrontUrl();
        }

        $model = $noty->getTarget();

        $baseFrontUrl = toSnake($model::class, true);

        if ($model instanceof Track) {
            $baseFrontUrl = Str::plural($model->track_type->value);
        }

        return url("$baseFrontUrl/$model->id");
    }
}
