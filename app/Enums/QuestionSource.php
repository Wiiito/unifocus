<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * De onde a questão veio.
 */
enum QuestionSource: string
{
    use HasOptions;

    case Ai = 'ai';
    case Manual = 'manual';
    case Imported = 'imported';

    public function label(): string
    {
        return match ($this) {
            self::Ai => __('IA'),
            self::Manual => __('Manual'),
            self::Imported => __('Importada'),
        };
    }
}
