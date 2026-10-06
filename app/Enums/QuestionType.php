<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Formato da questão.
 */
enum QuestionType: string
{
    use HasOptions;

    case MultipleChoice = 'multiple_choice';
    case TrueFalse = 'true_false';
    case Open = 'open';
    case Numeric = 'numeric';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => __('Múltipla escolha'),
            self::TrueFalse => __('Verdadeiro ou falso'),
            self::Open => __('Discursiva'),
            self::Numeric => __('Numérica'),
        };
    }

    /**
     * Questões cujo gabarito é uma alternativa marcada.
     */
    public function usesOptions(): bool
    {
        return in_array($this, [self::MultipleChoice, self::TrueFalse], true);
    }

    /**
     * Discursivas não têm correção automática: o estudante compara a própria
     * resposta com o gabarito comentado.
     */
    public function isAutoGraded(): bool
    {
        return $this !== self::Open;
    }
}
