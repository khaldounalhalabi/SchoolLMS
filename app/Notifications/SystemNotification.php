<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $titleKey,
        private readonly string $messageKey,
        private readonly ?string $url = null,
        private readonly string $notificationType = 'system',
        private readonly array $messageData = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title_key' => $this->titleKey,
            'message_key' => $this->messageKey,
            'message_data' => $this->messageData,
            'url' => $this->url,
            'type' => $this->notificationType,
        ];
    }
}
