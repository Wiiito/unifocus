<?php

namespace App\Livewire\Admin\Subjects;

use App\Livewire\Admin\Concerns\ManagesResourceForm;
use App\Livewire\Admin\Forms\SubjectForm;
use App\Models\Institution;
use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin')]
#[Title('Matérias')]
class Manager extends Component
{
    use ManagesResourceForm, WithPagination;

    public SubjectForm $form;

    #[Url]
    public string $search = '';

    /** '' = todas; 'general' = só catálogo geral; ou o ID da instituição. */
    #[Url]
    public string $institution = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'institution'], true)) {
            $this->resetPage();
        }
    }

    /**
     * @return LengthAwarePaginator<int, Subject>
     */
    #[Computed]
    public function subjects(): LengthAwarePaginator
    {
        return Subject::query()
            ->with('institution')
            ->withCount('classGroups')
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$this->search}%")
                ->orWhereLike('code', "%{$this->search}%")))
            ->when($this->institution === 'general', fn (Builder $query) => $query->whereNull('institution_id'))
            ->when(ctype_digit($this->institution), fn (Builder $query) => $query->where('institution_id', (int) $this->institution))
            ->latest()
            ->latest('id')
            ->paginate(10);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function institutions(): Collection
    {
        return Institution::query()->orderBy('name')->pluck('name', 'id');
    }

    public function render(): View
    {
        return view('livewire.admin.subjects.manager');
    }

    protected function findRecord(int $id): Model
    {
        return Subject::findOrFail($id);
    }

    /**
     * @return array{created: string, updated: string, deleted: string}
     */
    protected function feedbackMessages(): array
    {
        return [
            'created' => __('Matéria cadastrada com sucesso.'),
            'updated' => __('Matéria atualizada com sucesso.'),
            'deleted' => __('Matéria removida com sucesso.'),
        ];
    }
}
