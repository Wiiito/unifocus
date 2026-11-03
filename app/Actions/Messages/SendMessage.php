<?php

namespace App\Actions\Messages;

use App\Models\Message;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Ponto único de envio: só amigos conversam, então a regra mora aqui e vale
 * para qualquer tela que passe a enviar mensagens.
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

        return Message::create([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'body' => $body,
        ]);
    }
}
