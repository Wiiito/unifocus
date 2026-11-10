<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\User;
use App\Notifications\Channels\WebPushChannel;
use App\Notifications\Messages\WebPushMessage;

/**
 * Mensagem nova no chat. Vai só para o dispositivo: o chat já tem o próprio
 * contador de não lidas, e guardar cada mensagem no sino o inundaria. A tag
 * por remetente faz as mensagens de uma mesma conversa se substituírem.
 */
class NewDirectMessage extends PushNotification
{
    public function __construct(public Message $chatMessage, public User $sender) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    protected function message(object $notifiable): WebPushMessage
    {
        return new WebPushMessage(
            title: $this->sender->name,
            body: $this->chatMessage->body,
            url: route('messages.show', $this->sender->id),
            symbol: 'chat_bubble',
            tag: 'message-'.$this->sender->id,
            urgency: 'high',
        );
    }
}
