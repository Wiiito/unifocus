<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Moderação de uma questão.
 */
enum ReviewStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pendente'),
            self::Approved => __('Aprovada'),
            self::Rejected => __('Rejeitada'),
        };
    }
}
