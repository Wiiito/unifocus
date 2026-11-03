<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Situação de uma amizade: nasce como pedido e só vira amizade no aceite.
 * Recusar, cancelar ou desfazer apagam a linha, então não há outros estados.
 */
enum FriendshipStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Accepted = 'accepted';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pendente'),
            self::Accepted => __('Aceita'),
        };
    }
}
