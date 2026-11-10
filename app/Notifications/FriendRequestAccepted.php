<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Messages\WebPushMessage;

/**
 * O pedido de amizade que o estudante enviou foi aceito.
 */
class FriendRequestAccepted extends PushNotification
{
    public function __construct(public User $friend) {}

    protected function message(object $notifiable): WebPushMessage
    {
        return new WebPushMessage(
            title: __('Pedido de amizade aceito'),
            body: __(':name aceitou seu pedido. Que tal mandar um oi?', ['name' => $this->friend->name]),
            url: route('messages.show', $this->friend->id),
            symbol: 'group',
        );
    }
}
