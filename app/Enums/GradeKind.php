<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Origem de uma nota lançada.
 */
enum GradeKind: string
{
    use HasOptions;

    case Activity = 'activity';
    case Exam = 'exam';
    case Recovery = 'recovery';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Activity => __('Atividade'),
            self::Exam => __('Prova'),
            self::Recovery => __('Recuperação'),
            self::Manual => __('Avulsa'),
        };
    }
}
