<?php

namespace App\Contracts;

use App\Models\Question;
use App\Support\Questions\QuestionGenerationRequest;
use Illuminate\Support\Collection;

/**
 * Gera questões para o banco. A implementação com IA deve registrar a
 * chamada em ai_generations e gravar as questões pelo SaveQuestion com
 * source = ai e review_status = pending (a IA erra; um admin aprova).
 *
 * Enquanto ela não existe, o container resolve o UnavailableQuestionGenerator.
 */
interface QuestionGenerator
{
    public function isAvailable(): bool;

    /**
     * @return Collection<int, Question>
     */
    public function generate(QuestionGenerationRequest $request): Collection;
}
