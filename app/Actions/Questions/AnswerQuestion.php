<?php

namespace App\Actions\Questions;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Corrige a resposta de um estudante, registra a tentativa e atualiza as
 * estatísticas da questão.
 */
class AnswerQuestion
{
    /** Tolerância para respostas numéricas (arredondamentos). */
    private const NUMERIC_TOLERANCE = 0.01;

    public function handle(User $user, Question $question, ?int $optionId, ?string $answerText): QuestionAttempt
    {
        return DB::transaction(function () use ($user, $question, $optionId, $answerText): QuestionAttempt {
            $attempt = $user->questionAttempts()->create([
                'question_id' => $question->id,
                'question_option_id' => $question->type->usesOptions() ? $optionId : null,
                'answer_text' => $question->type->usesOptions() ? null : $answerText,
                'is_correct' => $this->isCorrect($question, $optionId, $answerText),
                'answered_at' => now(),
            ]);

            $this->refreshStatistics($question);

            return $attempt;
        });
    }

    private function isCorrect(Question $question, ?int $optionId, ?string $answerText): ?bool
    {
        return match ($question->type) {
            QuestionType::MultipleChoice, QuestionType::TrueFalse => $question->correctOption?->id === $optionId,
            QuestionType::Numeric => $this->matchesNumber($question->correctOption?->content, $answerText),
            QuestionType::Open => null,
        };
    }

    private function matchesNumber(?string $expected, ?string $given): bool
    {
        $toNumber = fn (?string $value): ?float => is_numeric($normalized = Str::of((string) $value)->trim()->replace(',', '.')->value())
            ? (float) $normalized
            : null;

        $expectedNumber = $toNumber($expected);
        $givenNumber = $toNumber($given);

        return $expectedNumber !== null && $givenNumber !== null
            && abs($expectedNumber - $givenNumber) <= self::NUMERIC_TOLERANCE;
    }

    /**
     * Caches de uso e taxa de acerto. Salvos sem eventos para não poluir a
     * auditoria da questão a cada resposta.
     */
    private function refreshStatistics(Question $question): void
    {
        $statistics = $question->attempts()
            ->toBase()
            ->selectRaw('COUNT(*) AS total, AVG(CASE WHEN is_correct THEN 100.0 WHEN is_correct IS NOT NULL THEN 0 END) AS rate')
            ->first();

        $question->forceFill([
            'times_used' => (int) $statistics->total,
            'correct_rate' => $statistics->rate === null ? null : round((float) $statistics->rate, 2),
        ])->saveQuietly();
    }
}
