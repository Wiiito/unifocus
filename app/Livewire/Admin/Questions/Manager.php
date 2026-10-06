<?php

namespace App\Livewire\Admin\Questions;

use App\Contracts\QuestionGenerator;
use App\Enums\ReviewStatus;
use App\Livewire\Admin\Concerns\ManagesResourceForm;
use App\Livewire\Admin\Forms\QuestionForm;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin')]
#[Title('Banco de questões')]
class Manager extends Component
{
    use ManagesResourceForm, WithPagination;

    public QuestionForm $form;

    #[Url]
    public string $search = '';

    #[Url]
    public string $subject = '';

    #[Url]
    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'subject', 'status'], true)) {
            $this->resetPage();
        }
    }

    /**
     * O Livewire só chama métodos do componente pelo front; estes repassam
     * para o formulário, que mantém a lista de alternativas.
     */
    public function addOption(): void
    {
        $this->form->addOption();
    }

    public function removeOption(int $index): void
    {
        $this->form->removeOption($index);
    }

    /**
     * Moderação rápida pela listagem (aprovar/rejeitar sem abrir o formulário).
     */
    public function setReviewStatus(int $id, string $status): void
    {
        validator(['status' => $status], ['status' => [Rule::enum(ReviewStatus::class)]])->validate();

        Question::findOrFail($id)->update(['review_status' => $status]);

        $this->flashMessage = __('Moderação atualizada.');
    }

    /**
     * @return LengthAwarePaginator<int, Question>
     */
    #[Computed]
    public function questions(): LengthAwarePaginator
    {
        return Question::query()
            ->with('subject')
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('statement', "%{$this->search}%")
                ->orWhereLike('topic', "%{$this->search}%")))
            ->when(ctype_digit($this->subject), fn (Builder $query) => $query->where('subject_id', (int) $this->subject))
            ->when(ReviewStatus::tryFrom($this->status), fn (Builder $query, ReviewStatus $status) => $query->where('review_status', $status))
            ->latest()
            ->latest('id')
            ->paginate(10);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function subjects(): Collection
    {
        return Subject::query()->orderBy('name')->pluck('name', 'id');
    }

    public function render(QuestionGenerator $generator): View
    {
        return view('livewire.admin.questions.manager', [
            'canGenerateWithAi' => $generator->isAvailable(),
        ]);
    }

    protected function findRecord(int $id): Model
    {
        return Question::with('options')->findOrFail($id);
    }

    /**
     * @return array{created: string, updated: string, deleted: string}
     */
    protected function feedbackMessages(): array
    {
        return [
            'created' => __('Questão cadastrada com sucesso.'),
            'updated' => __('Questão atualizada com sucesso.'),
            'deleted' => __('Questão removida com sucesso.'),
        ];
    }
}
