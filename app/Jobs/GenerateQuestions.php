<?php

namespace App\Jobs;

use App\Contracts\QuestionGenerator;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Subject;
use App\Support\Questions\QuestionGenerationRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Gera questões em segundo plano: a chamada à IA leva segundos e custa
 * dinheiro, então roda na fila, uma única vez (sem retry automático).
 */
class GenerateQuestions implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 150;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public Subject $subject,
        public ?Model $requestedBy = null,
        public int $count = 5,
        public QuestionType $type = QuestionType::MultipleChoice,
        public QuestionDifficulty $difficulty = QuestionDifficulty::Medium,
        public ?string $topic = null,
    ) {}

    public function handle(QuestionGenerator $generator): void
    {
        $generator->generate(new QuestionGenerationRequest(
            $this->subject,
            $this->requestedBy,
            $this->count,
            $this->type,
            $this->difficulty,
            $this->topic,
        ));
    }
}
