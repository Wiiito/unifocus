<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Presença do estudante em uma aula.
 */
enum AttendanceStatus: string
{
    use HasOptions;

    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Present => __('Presente'),
            self::Absent => __('Falta'),
            self::Late => __('Atraso'),
            self::Excused => __('Falta justificada'),
        };
    }

    /**
     * Só a falta sem justificativa entra na conta do limite de faltas; o
     * atraso conta como presença.
     */
    /**
     * Estados que contam como "foi à aula" (desafio diário).
     *
     * @return array<int, self>
     */
    public static function presences(): array
    {
        return [self::Present, self::Late];
    }

    public function countsAsAbsence(): bool
    {
        return $this === self::Absent;
    }
}
