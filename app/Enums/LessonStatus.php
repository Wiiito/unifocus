<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Situação de uma aula.
 */
enum LessonStatus: string
{
    use HasOptions;

    case Scheduled = 'scheduled';
    case Done = 'done';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('Agendada'),
            self::Done => __('Realizada'),
            self::Canceled => __('Cancelada'),
        };
    }
}
