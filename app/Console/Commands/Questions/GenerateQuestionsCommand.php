<?php

namespace App\Console\Commands\Questions;

use App\Contracts\QuestionGenerator;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Jobs\GenerateQuestions;
use App\Models\Subject;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

#[Signature('questions:generate {subject : ID ou código da matéria} {--count=5} {--type=multiple_choice} {--difficulty=medium} {--topic=} {--queue : Envia para a fila em vez de executar agora}')]
#[Description('Gera questões com IA para uma matéria (entram como pendentes de revisão)')]
class GenerateQuestionsCommand extends Command
{
    public function handle(QuestionGenerator $generator): int
    {
        if (! $generator->isAvailable()) {
            $this->components->error('A geração por IA está desligada. Defina AI_QUESTIONS_ENABLED=true e ANTHROPIC_API_KEY.');

            return self::FAILURE;
        }

        $validator = Validator::make($this->options(), [
            'count' => ['integer', 'between:1,'.config('ai.questions.max_per_request')],
            'type' => [Rule::enum(QuestionType::class)],
            'difficulty' => [Rule::enum(QuestionDifficulty::class)],
            'topic' => ['nullable', 'string', 'max:180'],
        ]);

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $subject = $this->findSubject((string) $this->argument('subject'));

        if ($subject === null) {
            $this->components->error('Matéria não encontrada.');

            return self::FAILURE;
        }

        $job = new GenerateQuestions(
            $subject,
            null,
            (int) $this->option('count'),
            QuestionType::from($this->option('type')),
            QuestionDifficulty::from($this->option('difficulty')),
            $this->option('topic') ?: null,
        );

        if ($this->option('queue')) {
            dispatch($job);
            $this->components->info("Geração para \"{$subject->name}\" enviada para a fila.");

            return self::SUCCESS;
        }

        dispatch_sync($job);
        $this->components->info("Questões geradas para \"{$subject->name}\". Revise-as no painel admin.");

        return self::SUCCESS;
    }

    private function findSubject(string $reference): ?Subject
    {
        return ctype_digit($reference)
            ? Subject::find((int) $reference)
            : Subject::where('code', $reference)->first();
    }
}
