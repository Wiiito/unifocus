<?php

namespace App\Support\Questions;

use App\Enums\QuestionDifficulty;
use App\Enums\QuestionType;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;

/**
 * O que se pede a um QuestionGenerator. requestedBy é o estudante ou admin
 * que disparou; fica nulo quando a geração parte de um comando.
 */
final readonly class QuestionGenerationRequest
{
    public function __construct(
        public Subject $subject,
        public ?Model $requestedBy = null,
        public int $count = 5,
        public QuestionType $type = QuestionType::MultipleChoice,
        public QuestionDifficulty $difficulty = QuestionDifficulty::Medium,
        public ?string $topic = null,
    ) {}
}
