<?php

namespace App\Livewire\Admin\AcademicTerms;

use App\Livewire\Admin\Concerns\ManagesResourceForm;
use App\Livewire\Admin\Forms\AcademicTermForm;
use App\Models\AcademicTerm;
use App\Models\Institution;
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
#[Title('Períodos letivos')]
class Manager extends Component
{
    use ManagesResourceForm, WithPagination;

    public AcademicTermForm $form;

    #[Url]
    public string $institution = '';

    public function updatedInstitution(): void
    {
        $this->resetPage();
    }

    /**
     * Novo período já vem com a instituição do filtro selecionada.
     */
    public function openCreateForm(): void
    {
        $this->closeForm();

        $this->form->institutionId = ctype_digit($this->institution) ? (int) $this->institution : null;
        $this->showForm = true;
    }

    /**
     * O Livewire só chama métodos do componente pelo front; estes repassam
     * para o formulário, que mantém a lista de marcos.
     */
    public function addEvent(): void
    {
        $this->form->addEvent();
    }

    public function removeEvent(int $index): void
    {
        $this->form->removeEvent($index);
    }

    /**
     * @return LengthAwarePaginator<int, AcademicTerm>
     */
    #[Computed]
    public function terms(): LengthAwarePaginator
    {
        return AcademicTerm::query()
            ->with('institution')
            ->withCount(['events', 'classGroups'])
            ->when(ctype_digit($this->institution), fn (Builder $query) => $query->where('institution_id', (int) $this->institution))
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
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
        return view('livewire.admin.academic-terms.manager');
    }

    protected function findRecord(int $id): Model
    {
        return AcademicTerm::with('events')->findOrFail($id);
    }

    /**
     * @return array{created: string, updated: string, deleted: string}
     */
    protected function feedbackMessages(): array
    {
        return [
            'created' => __('Período cadastrado com sucesso.'),
            'updated' => __('Período atualizado com sucesso.'),
            'deleted' => __('Período removido com sucesso.'),
        ];
    }
}
