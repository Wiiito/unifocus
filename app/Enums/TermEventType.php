<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Tipo de marco no calendário de um período letivo.
 */
enum TermEventType: string
{
    use HasOptions;

    case ExamWeek = 'exam_week';
    case Holiday = 'holiday';
    case Recess = 'recess';
    case Enrollment = 'enrollment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ExamWeek => __('Semana de provas'),
            self::Holiday => __('Feriado'),
            self::Recess => __('Recesso'),
            self::Enrollment => __('Matrículas'),
            self::Other => __('Outro'),
        };
    }
}
