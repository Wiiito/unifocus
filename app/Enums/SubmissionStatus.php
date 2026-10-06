<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Estado da entrega de uma atividade.
 */
enum SubmissionStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Submitted = 'submitted';
    case Late = 'late';
    case Graded = 'graded';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pendente'),
            self::Submitted => __('Entregue'),
            self::Late => __('Entregue com atraso'),
            self::Graded => __('Corrigida'),
            self::Returned => __('Devolvida'),
        };
    }

    /**
     * Estados em que a atividade já saiu da lista de pendências do estudante.
     */
    public function isDelivered(): bool
    {
        return $this !== self::Pending;
    }
}
