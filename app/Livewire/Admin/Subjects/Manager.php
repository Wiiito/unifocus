<?php

namespace App\Livewire\Admin\Subjects;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::admin')]
#[Title('Matérias')]
class Manager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingSubjectId = null;

    public string $name = '';

    public ?string $code = null;

    public ?string $description = null;

    /**
     * Untyped on purpose: number inputs send their value as a string over
     * the wire, and the `integer` validation rule (plus the model's cast)
     * takes care of turning it into an int before it reaches the database.
     */
    public $credits = null;

    public $workloadHours = null;

    public ?string $color = null;

    public ?string $flashMessage = null;

    /**
     * @return LengthAwarePaginator<int, Subject>
     */
    #[Computed]
    public function subjects()
    {
        return Subject::query()->latest()->paginate(10);
    }

    public function openCreateForm(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function openEditForm(int $subjectId): void
    {
        $subject = Subject::findOrFail($subjectId);

        $this->resetValidation();

        $this->editingSubjectId = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code;
        $this->description = $subject->description;
        $this->credits = $subject->credits;
        $this->workloadHours = $subject->workload_hours;
        $this->color = $subject->color;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $attributes = [
            'name' => $validated['name'],
            'code' => $validated['code'] !== '' ? $validated['code'] : null,
            'description' => $validated['description'] !== '' ? $validated['description'] : null,
            'credits' => $validated['credits'],
            'workload_hours' => $validated['workloadHours'],
            'color' => $validated['color'] !== '' ? $validated['color'] : null,
        ];

        if ($this->editingSubjectId) {
            Subject::findOrFail($this->editingSubjectId)->update($attributes);

            $this->flashMessage = __('Matéria atualizada com sucesso.');
        } else {
            $attributes['created_by_admin_id'] = Auth::guard('admin')->id();

            Subject::create($attributes);

            $this->flashMessage = __('Matéria cadastrada com sucesso.');
        }

        $this->resetForm();
    }

    public function deleteSubject(int $subjectId): void
    {
        $subject = Subject::findOrFail($subjectId);
        $subject->delete();

        if ($this->editingSubjectId === $subjectId) {
            $this->resetForm();
        }

        $this->flashMessage = __('Matéria removida com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.admin.subjects.manager');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('subjects', 'code')->ignore($this->editingSubjectId)],
            'description' => ['nullable', 'string'],
            'credits' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'workloadHours' => ['nullable', 'integer', 'min:0', 'max:32767'],
            'color' => ['nullable', 'string', 'max:9', 'regex:/^#[0-9A-Fa-f]{6,8}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'workloadHours' => __('carga horária'),
        ];
    }

    private function resetForm(): void
    {
        $this->reset(['showForm', 'editingSubjectId', 'name', 'code', 'description', 'credits', 'workloadHours', 'color']);

        $this->resetValidation();
    }
}
