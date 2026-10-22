<?php

namespace App\Support\Questions;

use App\Enums\QuestionType;

/**
 * Monta o prompt de geração. Separado do cliente HTTP para que trocar de
 * provedor não mude o que é pedido (nem o formato que o parser espera).
 */
class QuestionPromptBuilder
{
    public function system(): string
    {
        return <<<'PROMPT'
Você é um professor universitário que elabora questões de estudo em português do Brasil.
Escreva questões corretas, claras, autocontidas e sem ambiguidade. Não repita enunciados.
Responda SOMENTE com um objeto JSON válido, sem texto antes ou depois e sem blocos de código, neste formato:
{"questions":[{"statement":"enunciado","topic":"tópico curto","explanation":"gabarito comentado","options":[{"content":"texto","is_correct":true}]}]}
PROMPT;
    }

    public function user(QuestionGenerationRequest $request, int $count): string
    {
        $subject = $request->subject;

        $lines = [
            "Gere {$count} questões da matéria \"{$subject->name}\"".($subject->code ? " ({$subject->code})" : '').'.',
            "Dificuldade: {$request->difficulty->label()}.",
            'Formato: '.$this->typeInstructions($request->type),
        ];

        if (filled($subject->description)) {
            $lines[] = "Ementa da matéria: {$subject->description}";
        }

        if (filled($request->topic)) {
            $lines[] = "Concentre todas as questões no tópico: {$request->topic}.";
        }

        $lines[] = 'Em "explanation", explique por que a resposta está correta.';

        return implode("\n", $lines);
    }

    private function typeInstructions(QuestionType $type): string
    {
        return match ($type) {
            QuestionType::MultipleChoice => 'múltipla escolha, com exatamente 4 alternativas em "options" e apenas uma com is_correct true.',
            QuestionType::TrueFalse => 'verdadeiro ou falso: o enunciado é uma afirmação e "options" tem exatamente duas alternativas, "Verdadeiro" e "Falso", com apenas uma is_correct true.',
            QuestionType::Numeric => 'resposta numérica: "options" tem exatamente uma alternativa, is_correct true, cujo "content" é somente o número (sem unidade nem texto).',
            QuestionType::Open => 'discursiva: "options" é uma lista vazia e "explanation" traz a resposta de referência completa.',
        };
    }
}
