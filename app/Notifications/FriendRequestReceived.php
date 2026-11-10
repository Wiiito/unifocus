<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Messages\WebPushMessage;

/**
 * Alguém pediu amizade ao estudante.
 */
class FriendRequestReceived extends PushNotification
{
    public function __construct(public User $requester) {}

    protected function message(object $notifiable): WebPushMessage
    {
        return new WebPushMessage(
            title: __('Novo pedido de amizade'),
            body: __(':name quer ser seu amigo no UniFocus.', ['name' => $this->requester->name]),
            url: route('friends.index'),
            symbol: 'person_add',
        );
    }
}
