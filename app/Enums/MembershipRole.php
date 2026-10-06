<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Papel da pessoa dentro de uma instituição.
 */
enum MembershipRole: string
{
    use HasOptions;

    case Student = 'student';
    case Teacher = 'teacher';
    case Coordinator = 'coordinator';

    public function label(): string
    {
        return match ($this) {
            self::Student => __('Estudante'),
            self::Teacher => __('Professor(a)'),
            self::Coordinator => __('Coordenador(a)'),
        };
    }
}
