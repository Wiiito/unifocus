<?php

namespace App\Support\Questions;

use App\Contracts\QuestionGenerator;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Implementação padrão enquanto a geração por IA não é ligada: as questões
 * são cadastradas manualmente pelo painel admin.
 */
class UnavailableQuestionGenerator implements QuestionGenerator
{
    public function isAvailable(): bool
    {
        return false;
    }

    public function generate(QuestionGenerationRequest $request): Collection
    {
        throw new RuntimeException('A geração de questões por IA ainda não está disponível.');
    }
}
