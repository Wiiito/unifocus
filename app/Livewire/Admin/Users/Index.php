<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Consulta de estudantes. Somente leitura: os dados acadêmicos são do
 * próprio estudante.
 */
#[Layout('layouts::admin')]
#[Title('Estudantes')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with('institutions:id,name')
            ->withCount([
                'enrollments as ongoing_enrollments_count' => fn (Builder $query) => $query->ongoing(),
                'questionAttempts',
            ])
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('name', "%{$this->search}%")
                ->orWhereLike('email', "%{$this->search}%")))
            ->latest()
            ->latest('id')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.users.index');
    }
}
