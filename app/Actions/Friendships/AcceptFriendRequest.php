<?php

namespace App\Actions\Friendships;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;

/**
 * Aceita um pedido pendente. Quem pode aceitar (o destinatário) é decidido
 * por quem chama; recusar, cancelar e desfazer são simplesmente apagar a linha.
 */
class AcceptFriendRequest
{
    public function handle(Friendship $friendship): Friendship
    {
        if (! $friendship->isAccepted()) {
            $friendship->update([
                'status' => FriendshipStatus::Accepted,
                'accepted_at' => now(),
            ]);
        }

        return $friendship;
    }
}
