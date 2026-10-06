<?php

namespace App\Livewire\Admin\Forms;

use App\Actions\Questions\SaveQuestion;
use App\Enums\QuestionDifficulty;
use App\Enums\QuestionSource;
use App\Enums\QuestionType;
use App\Enums\ReviewStatus;
use App\Models\Question;
use App\Models\QuestionOption;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Cadastro manual de questões. O formato do gabarito muda com o tipo:
 * alternativas (múltipla escolha), verdadeiro/falso, valor (numérica) ou só o
 * gabarito comentado (discursiva). Tudo é gravado pelo SaveQuestion.
 */
class QuestionForm extends ResourceForm
{
    private const MIN_OPTIONS = 2;

    private const MAX_OPTIONS = 6;

    public $subjectId = null;

    public string $type = 'multiple_choice';

    public string $difficulty = 'medium';

    public string $reviewStatus = 'approved';

    public ?string $topic = null;

    public string $statement = '';

    public ?string $explanation = null;

    /**
     * @var array<int, array{content: string}>
     */
    public array $options = [['content' => ''], ['content' => ''], ['content' => ''], ['content' => '']];

    public $correctOption = 0;

    public string $trueFalseAnswer = 'true';

    public ?string $numericAnswer = null;

    public function addOption(): void
    {
        if (count($this->options) < self::MAX_OPTIONS) {
            $this->options[] = ['content' => ''];
        }
    }

    public function removeOption(int $index): void
    {
        if (count($this->options) <= self::MIN_OPTIONS) {
            return;
        }

        unset($this->options[$index]);
        $this->options = array_values($this->options);

        if ((int) $this->correctOption >= count($this->options)) {
            $this->correctOption = 0;
        }
    }

    public function questionType(): QuestionType
    {
        return QuestionType::from($this->type);
    }

    protected function fillFromRecord(Model $record): void
    {
        /** @var Question $record */
        $this->subjectId = $record->subject_id;
        $this->type = $record->type->value;
        $this->difficulty = $record->difficulty->value;
        $this->reviewStatus = $record->review_status->value;
        $this->topic = $record->topic;
        $this->statement = $record->statement;
        $this->explanation = $record->explanation;

        $options = $record->options;

        if ($record->type === QuestionType::MultipleChoice) {
            $this->options = $options->map(fn (QuestionOption $option): array => ['content' => $option->content])->all();
            $this->correctOption = (int) $options->search(fn (QuestionOption $option) => $option->is_correct);
        }

        /** Verdadeiro/falso é gravado como [Verdadeiro, Falso]: basta ver qual é a correta. */
        if ($record->type === QuestionType::TrueFalse) {
            $this->trueFalseAnswer = $options->first()?->is_correct === false ? 'false' : 'true';
        }

        if ($record->type === QuestionType::Numeric) {
            $this->numericAnswer = $options->first()?->content;
        }
    }

    protected function persist(): Model
    {
        $attributes = [
            'subject_id' => $this->subjectId,
            'type' => $this->type,
            'difficulty' => $this->difficulty,
            'review_status' => $this->reviewStatus,
            'topic' => $this->nullable($this->topic),
            'statement' => trim($this->statement),
            'explanation' => $this->nullable($this->explanation),
        ];

        $question = $this->isEditing() ? Question::findOrFail($this->editingId) : null;

        if ($question === null) {
            $attributes['source'] = QuestionSource::Manual;
            $attributes['created_by_admin_id'] = Auth::guard('admin')->id();
        }

        return app(SaveQuestion::class)->handle($attributes, $this->normalizedOptions(), $question);
    }

    /**
     * @return array<int, array{content: string, is_correct: bool}>
     */
    private function normalizedOptions(): array
    {
        return match ($this->questionType()) {
            QuestionType::MultipleChoice => collect($this->options)
                ->map(fn (array $option, int $index): array => [
                    'content' => trim($option['content']),
                    'is_correct' => $index === (int) $this->correctOption,
                ])
                ->all(),
            QuestionType::TrueFalse => [
                ['content' => __('Verdadeiro'), 'is_correct' => $this->trueFalseAnswer === 'true'],
                ['content' => __('Falso'), 'is_correct' => $this->trueFalseAnswer === 'false'],
            ],
            QuestionType::Numeric => [['content' => trim((string) $this->numericAnswer), 'is_correct' => true]],
            QuestionType::Open => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $type = QuestionType::tryFrom($this->type);

        return [
            'subjectId' => ['required', 'integer', Rule::exists('subjects', 'id')->whereNull('deleted_at')],
            'type' => ['required', Rule::enum(QuestionType::class)],
            'difficulty' => ['required', Rule::enum(QuestionDifficulty::class)],
            'reviewStatus' => ['required', Rule::enum(ReviewStatus::class)],
            'topic' => ['nullable', 'string', 'max:180'],
            'statement' => ['required', 'string', 'max:5000', $this->uniqueStatementRule()],
            'explanation' => [Rule::requiredIf($type === QuestionType::Open), 'nullable', 'string', 'max:5000'],
            ...match ($type) {
                QuestionType::MultipleChoice => [
                    'options' => ['array', 'min:'.self::MIN_OPTIONS, 'max:'.self::MAX_OPTIONS],
                    'options.*.content' => ['required', 'string', 'max:1000'],
                    'correctOption' => ['required', 'integer', 'min:0', 'max:'.(count($this->options) - 1)],
                ],
                QuestionType::TrueFalse => ['trueFalseAnswer' => ['required', Rule::in(['true', 'false'])]],
                QuestionType::Numeric => ['numericAnswer' => ['required', 'numeric']],
                default => [],
            },
        ];
    }

    /**
     * O mesmo enunciado (ignorando caixa e espaços) não entra duas vezes.
     */
    private function uniqueStatementRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $isDuplicate = Question::withTrashed()
                ->where('statement_hash', Question::hashStatement((string) $value))
                ->when($this->editingId, fn ($query) => $query->whereKeyNot($this->editingId))
                ->exists();

            if ($isDuplicate) {
                $fail(__('Já existe uma questão com este enunciado no banco.'));
            }
        };
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'subjectId' => __('matéria'),
            'type' => __('tipo'),
            'difficulty' => __('dificuldade'),
            'reviewStatus' => __('moderação'),
            'topic' => __('tópico'),
            'statement' => __('enunciado'),
            'explanation' => __('gabarito comentado'),
            'options.*.content' => __('alternativa'),
            'correctOption' => __('alternativa correta'),
            'numericAnswer' => __('resposta'),
        ];
    }
}
