<?php

namespace App\Actions\Messages;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewDirectMessage;
use Illuminate\Validation\ValidationException;

/**
 * Ponto único de envio: só amigos conversam, então a regra mora aqui e vale
 * para qualquer tela que passe a enviar mensagens. Avisa o destinatário por push.
 */
class SendMessage
{
    /**
     * @throws ValidationException
     */
    public function handle(User $sender, User $recipient, string $body): Message
    {
        if (! $sender->friends()->whereKey($recipient->id)->exists()) {
            throw ValidationException::withMessages(['body' => __('Você só pode conversar com seus amigos.')]);
        }

        $message = Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'body' => $body,
        ]);

        $recipient->notify(new NewDirectMessage($message, $sender));

        return $message;
    }
}
