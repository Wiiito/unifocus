<?php

namespace App\Notifications;

use App\Notifications\Messages\WebPushMessage;

/**
 * Notificação genérica do comando notifications:test, para conferir se o
 * sino e o push de um estudante estão funcionando.
 */
class TestNotification extends PushNotification
{
    protected function message(object $notifiable): WebPushMessage
    {
        return new WebPushMessage(
            title: __('Notificação de teste'),
            body: __('Se você está vendo isto, as notificações do UniFocus estão funcionando.'),
            url: route('dashboard'),
            symbol: 'notifications_active',
            tag: 'test',
        );
    }
}
