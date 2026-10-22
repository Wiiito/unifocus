<?php

namespace App\Support\Questions;

use App\Enums\QuestionType;
use Illuminate\Support\Str;

/**
 * Converte a resposta textual da IA em questões prontas para o SaveQuestion.
 * A IA erra o formato com frequência: o que não cumpre as regras do tipo é
 * descartado (e o gerador falha se nada sobrar), nunca "consertado".
 */
class GeneratedQuestionParser
{
    /**
     * @return array<int, array{statement: string, topic: ?string, explanation: ?string, options: array<int, array{content: string, is_correct: bool}>}>
     */
    public function parse(string $text, QuestionType $type): array
    {
        $data = $this->decode($text);

        $items = is_array($data['questions'] ?? null) ? $data['questions'] : (array_is_list($data) ? $data : []);

        $parsed = [];

        foreach ($items as $item) {
            $question = is_array($item) ? $this->normalize($item, $type) : null;

            if ($question !== null) {
                $parsed[] = $question;
            }
        }

        return $parsed;
    }

    /**
     * @return array<mixed>
     */
    private function decode(string $text): array
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        if ($start === false || $end === false || $end < $start) {
            return [];
        }

        $decoded = json_decode(substr($text, $start, $end - $start + 1), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<mixed>  $item
     * @return array{statement: string, topic: ?string, explanation: ?string, options: array<int, array{content: string, is_correct: bool}>}|null
     */
    private function normalize(array $item, QuestionType $type): ?array
    {
        $statement = $this->text($item['statement'] ?? null);
        $explanation = $this->text($item['explanation'] ?? null);

        if ($statement === null) {
            return null;
        }

        $options = $this->options($item['options'] ?? []);

        if (! $this->isValidFor($type, $options, $explanation)) {
            return null;
        }

        return [
            'statement' => $statement,
            'topic' => $this->text($item['topic'] ?? null),
            'explanation' => $explanation,
            'options' => $type === QuestionType::Open ? [] : $options,
        ];
    }

    /**
     * @return array<int, array{content: string, is_correct: bool}>
     */
    private function options(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $options = [];

        foreach ($raw as $option) {
            $content = is_array($option) ? $this->text($option['content'] ?? null) : null;

            if ($content !== null) {
                $options[] = ['content' => $content, 'is_correct' => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN)];
            }
        }

        return $options;
    }

    /**
     * @param  array<int, array{content: string, is_correct: bool}>  $options
     */
    private function isValidFor(QuestionType $type, array $options, ?string $explanation): bool
    {
        $correct = count(array_filter($options, fn (array $option): bool => $option['is_correct']));

        return match ($type) {
            QuestionType::MultipleChoice => count($options) >= 2 && $correct === 1,
            QuestionType::TrueFalse => count($options) === 2 && $correct === 1,
            QuestionType::Numeric => count($options) === 1 && $correct === 1
                && is_numeric(Str::of($options[0]['content'])->replace(',', '.')->value()),
            QuestionType::Open => $explanation !== null,
        };
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
