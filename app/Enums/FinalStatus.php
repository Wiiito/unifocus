<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Resultado da matrícula, sempre calculado (nunca editado à mão).
 */
enum FinalStatus: string
{
    use HasOptions;

    case InProgress = 'in_progress';
    case Approved = 'approved';
    case Failed = 'failed';
    case FailedAbsence = 'failed_absence';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => __('Em andamento'),
            self::Approved => __('Aprovado'),
            self::Failed => __('Reprovado por nota'),
            self::FailedAbsence => __('Reprovado por falta'),
        };
    }

    /**
     * Variante do componente de badge que representa este resultado.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::InProgress => 'neutral',
            self::Approved => 'success',
            self::Failed, self::FailedAbsence => 'danger',
        };
    }
}
