<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

/**
 * Ciclo de vida de uma chamada de IA.
 */
enum AiGenerationStatus: string
{
    use HasOptions;

    case Queued = 'queued';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => __('Na fila'),
            self::Running => __('Executando'),
            self::Succeeded => __('Concluída'),
            self::Failed => __('Falhou'),
        };
    }
}
