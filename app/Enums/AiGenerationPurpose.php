<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Finalidade de uma chamada de IA.
 */
enum AiGenerationPurpose: string
{
    use HasOptions;

    case Questions = 'questions';
    case Summary = 'summary';
    case Flashcards = 'flashcards';
    case Explanation = 'explanation';

    public function label(): string
    {
        return match ($this) {
            self::Questions => __('Questões'),
            self::Summary => __('Resumo'),
            self::Flashcards => __('Flashcards'),
            self::Explanation => __('Explicação'),
        };
    }
}
