<?php

namespace App\Support\Questions;

/**
 * Resposta de um provedor de IA já reduzida ao que o gerador usa. costCents
 * é nulo quando o provedor não informa o custo.
 */
final readonly class QuestionCompletion
{
    public function __construct(
        public string $text,
        public ?int $inputTokens = null,
        public ?int $outputTokens = null,
        public ?int $costCents = null,
    ) {}
}
