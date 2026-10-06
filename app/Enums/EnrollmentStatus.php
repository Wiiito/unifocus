<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Situação da matrícula, controlada pelo estudante.
 */
enum EnrollmentStatus: string
{
    use HasOptions;

    case Active = 'active';
    case Completed = 'completed';
    case Dropped = 'dropped';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Cursando'),
            self::Completed => __('Concluída'),
            self::Dropped => __('Desistência'),
            self::Locked => __('Trancada'),
        };
    }
}
