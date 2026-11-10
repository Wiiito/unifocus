<?php

namespace App\Actions\Friendships;

use App\Enums\FriendshipStatus;
use App\Models\Friendship;
use App\Models\User;
use App\Notifications\FriendRequestReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pede amizade. Se a outra pessoa já tinha pedido a mesma coisa, os dois
 * querem: o pedido dela é aceito em vez de criar um segundo vínculo. O
 * destinatário de um pedido novo é avisado.
 */
class SendFriendRequest
{
    /**
     * @throws ValidationException
     */
    public function handle(User $requester, User $addressee): Friendship
    {
        if ($requester->is($addressee)) {
            throw ValidationException::withMessages(['email' => __('Você não pode adicionar a si mesmo.')]);
        }

        $friendship = DB::transaction(function () use ($requester, $addressee): Friendship {
            $existing = Friendship::query()->between($requester, $addressee)->lockForUpdate()->first();

            if ($existing === null) {
                return Friendship::create([
                    'requester_id' => $requester->id,
                    'addressee_id' => $addressee->id,
                    'status' => FriendshipStatus::Pending,
                ]);
            }

            if ($existing->isAccepted()) {
                throw ValidationException::withMessages(['email' => __('Vocês já são amigos.')]);
            }

            if ($existing->requester_id === $requester->id) {
                throw ValidationException::withMessages(['email' => __('Você já enviou um pedido para esta pessoa.')]);
            }

            return (new AcceptFriendRequest)->handle($existing);
        });

        if ($friendship->wasRecentlyCreated) {
            $addressee->notify(new FriendRequestReceived($requester));
        }

        return $friendship;
    }
}
