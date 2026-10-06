<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Dificuldade da questão.
 */
enum QuestionDifficulty: string
{
    use HasOptions;

    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function label(): string
    {
        return match ($this) {
            self::Easy => __('Fácil'),
            self::Medium => __('Média'),
            self::Hard => __('Difícil'),
        };
    }
}
