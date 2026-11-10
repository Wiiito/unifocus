<?php

namespace App\Notifications;

use App\Notifications\Channels\WebPushChannel;
use App\Notifications\Messages\WebPushMessage;
use Illuminate\Notifications\Notification;

/**
 * Base das notificações do estudante: o mesmo conteúdo vai para o sino
 * (database) e para os dispositivos inscritos (Web Push).
 *
 * Quando a notificação também fica no sino, o clique no push passa por
 * notifications.show, que a marca como lida antes de levar ao destino.
 */
abstract class PushNotification extends Notification
{
    abstract protected function message(object $notifiable): WebPushMessage;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        $message = $this->message($notifiable);

        return in_array('database', $this->via($notifiable), true)
            ? $message->withUrl(route('notifications.show', $this->id))
            : $message;
    }

    /**
     * @return array{title: string, body: string, url: string, symbol: string}
     */
    public function toArray(object $notifiable): array
    {
        return $this->message($notifiable)->toArray();
    }
}
