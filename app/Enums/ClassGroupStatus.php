<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Situação da turma.
 */
enum ClassGroupStatus: string
{
    use HasOptions;

    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => __('Planejada'),
            self::InProgress => __('Em andamento'),
            self::Finished => __('Encerrada'),
            self::Canceled => __('Cancelada'),
        };
    }
}
