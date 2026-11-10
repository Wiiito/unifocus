<?php

namespace App\Notifications\Channels;

use App\Jobs\SendWebPush;
use App\Models\User;
use App\Notifications\PushNotification;
use App\Support\WebPush\WebPushSender;

/**
 * Canal de notificação que leva a mensagem a todos os navegadores inscritos
 * da pessoa. Cada entrega vira um job: o POST no serviço de push não segura
 * a requisição de quem disparou a notificação. Sem chaves VAPID, não faz nada.
 */
class WebPushChannel
{
    public function __construct(private WebPushSender $sender) {}

    public function send(User $notifiable, PushNotification $notification): void
    {
        if (! $this->sender->isConfigured()) {
            return;
        }

        $subscriptions = $notifiable->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $message = $notification->toWebPush($notifiable);

        foreach ($subscriptions as $subscription) {
            SendWebPush::dispatch($subscription, $message)->afterCommit();
        }
    }
}
