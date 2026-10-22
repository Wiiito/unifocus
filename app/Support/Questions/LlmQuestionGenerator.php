<?php

namespace App\Support\Questions;

use App\Actions\Questions\SaveQuestion;
use App\Contracts\QuestionGenerator;
use App\Enums\AiGenerationPurpose;
use App\Enums\AiGenerationStatus;
use App\Enums\QuestionSource;
use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\AiGeneration;
use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Parte comum dos geradores por LLM: monta o prompt, registra a chamada em
 * ai_generations (tokens, custo, latência, erro) e grava as questões pelo
 * SaveQuestion como source = ai e review_status = pending, para um admin
 * aprovar antes de qualquer estudante ver. Cada provedor só implementa a
 * chamada HTTP (complete) e informa sua chave.
 */
abstract class LlmQuestionGenerator implements QuestionGenerator
{
    public function __construct(
        protected SaveQuestion $saveQuestion,
        protected QuestionPromptBuilder $prompts,
        protected GeneratedQuestionParser $parser,
    ) {}

    /** Nome gravado em ai_generations.provider. */
    abstract protected function provider(): string;

    abstract protected function model(): string;

    abstract protected function complete(string $system, string $user): QuestionCompletion;

    /**
     * Questões novas gravadas. Enunciados que já existem no banco são
     * ignorados (statement_hash único), então pode devolver menos que o pedido.
     *
     * @return Collection<int, Question>
     */
    public function generate(QuestionGenerationRequest $request): Collection
    {
        if (! $this->isAvailable()) {
            throw new RuntimeException('A geração de questões por IA não está configurada.');
        }

        $count = max(1, min($request->count, (int) config('ai.questions.max_per_request')));
        $system = $this->prompts->system();
        $user = $this->prompts->user($request, $count);

        $generation = $this->startGeneration($request, $system, $user);
        $startedAt = hrtime(true);

        try {
            $completion = $this->complete($system, $user);

            $parsed = $this->parser->parse($completion->text, $request->type);

            if ($parsed === []) {
                throw new RuntimeException('A IA não devolveu nenhuma questão válida.');
            }

            $questions = DB::transaction(fn (): Collection => $this->store($parsed, $request, $generation));
        } catch (Throwable $exception) {
            $generation->update([
                'status' => AiGenerationStatus::Failed,
                'latency_ms' => $this->elapsedMs($startedAt),
                'error' => Str::limit($exception->getMessage(), 1000, ''),
            ]);

            throw $exception;
        }

        $generation->update([
            'status' => AiGenerationStatus::Succeeded,
            'tokens_input' => $completion->inputTokens,
            'tokens_output' => $completion->outputTokens,
            'cost_cents' => $completion->costCents,
            'latency_ms' => $this->elapsedMs($startedAt),
        ]);

        return $questions;
    }

    private function startGeneration(QuestionGenerationRequest $request, string $system, string $user): AiGeneration
    {
        $generation = new AiGeneration([
            'subject_id' => $request->subject->id,
            'purpose' => AiGenerationPurpose::Questions,
            'provider' => $this->provider(),
            'model' => $this->model(),
            'prompt' => $system."\n\n".$user,
            'prompt_hash' => hash('sha256', $system."\n".$user),
            'status' => AiGenerationStatus::Running,
        ]);

        if ($request->requestedBy !== null) {
            $generation->requestedBy()->associate($request->requestedBy);
        }

        $generation->save();

        return $generation;
    }

    /**
     * @param  array<int, array{statement: string, topic: ?string, explanation: ?string, options: array<int, array{content: string, is_correct: bool}>}>  $parsed
     * @return Collection<int, Question>
     */
    private function store(array $parsed, QuestionGenerationRequest $request, AiGeneration $generation): Collection
    {
        $questions = new Collection;

        foreach ($parsed as $item) {
            if (Question::withTrashed()->where('statement_hash', Question::hashStatement($item['statement']))->exists()) {
                continue;
            }

            $questions->push($this->saveQuestion->handle([
                'subject_id' => $request->subject->id,
                'created_by_admin_id' => $request->requestedBy instanceof Admin ? $request->requestedBy->id : null,
                'ai_generation_id' => $generation->id,
                'statement' => $item['statement'],
                'type' => $request->type,
                'difficulty' => $request->difficulty,
                'topic' => Str::limit($item['topic'] ?? $request->topic ?? '', 180, '') ?: null,
                'explanation' => $item['explanation'],
                'source' => QuestionSource::Ai,
                'review_status' => ReviewStatus::Pending,
            ], $item['options']));
        }

        return $questions;
    }

    private function elapsedMs(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }
}
