<?php

namespace App\Livewire\Admin\Institutions;

use App\Livewire\Admin\Concerns\ManagesResourceForm;
use App\Livewire\Admin\Forms\InstitutionForm;
use App\Models\Institution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin')]
#[Title('Instituições')]
class Manager extends Component
{
    use ManagesResourceForm, WithPagination;

    public InstitutionForm $form;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Institution>
     */
    #[Computed]
    public function institutions(): LengthAwarePaginator
    {
        return Institution::query()
            ->withCount(['members', 'subjects', 'academicTerms'])
            ->when($this->search !== '', fn (Builder $query) => $query->whereLike('name', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.admin.institutions.manager');
    }

    protected function findRecord(int $id): Model
    {
        return Institution::findOrFail($id);
    }

    /**
     * @return array{created: string, updated: string, deleted: string}
     */
    protected function feedbackMessages(): array
    {
        return [
            'created' => __('Instituição cadastrada com sucesso.'),
            'updated' => __('Instituição atualizada com sucesso.'),
            'deleted' => __('Instituição removida com sucesso.'),
        ];
    }
}
