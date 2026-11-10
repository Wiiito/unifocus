<?php

namespace App\Actions\Friendships;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Notifications\FriendRequestAccepted;

/**
 * Aceita um pedido pendente. Quem pode aceitar (o destinatário) é decidido
 * por quem chama; recusar, cancelar e desfazer são simplesmente apagar a linha.
 * Quem fez o pedido é avisado de que ele foi aceito.
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

            $friendship->requester->notify(new FriendRequestAccepted($friendship->addressee));
        }

        return $friendship;
    }
}
