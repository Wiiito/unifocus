<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Situação do vínculo com a instituição.
 */
enum MembershipStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Ativo'),
            self::Inactive => __('Inativo'),
            self::Pending => __('Pendente'),
        };
    }
}
