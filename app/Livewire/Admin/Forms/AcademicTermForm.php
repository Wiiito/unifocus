<?php

namespace App\Livewire\Admin\Forms;

use App\Enums\TermEventType;
use App\Models\AcademicTerm;
use App\Models\AcademicTermEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Período letivo com os marcos do calendário (semana de provas, recesso...)
 * editados no mesmo formulário.
 */
class AcademicTermForm extends ResourceForm
{
    public $institutionId = null;

    public string $name = '';

    public ?string $startsOn = null;

    public ?string $endsOn = null;

    /**
     * @var array<int, array{title: string, type: string, starts_on: string|null, ends_on: string|null}>
     */
    public array $events = [];

    public function addEvent(): void
    {
        $this->events[] = ['title' => '', 'type' => TermEventType::ExamWeek->value, 'starts_on' => null, 'ends_on' => null];
    }

    public function removeEvent(int $index): void
    {
        unset($this->events[$index]);

        $this->events = array_values($this->events);
    }

    protected function fillFromRecord(Model $record): void
    {
        /** @var AcademicTerm $record */
        $this->institutionId = $record->institution_id;
        $this->name = $record->name;
        $this->startsOn = $record->starts_on->toDateString();
        $this->endsOn = $record->ends_on->toDateString();
        $this->events = $record->events->map(fn (AcademicTermEvent $event): array => [
            'title' => $event->title,
            'type' => $event->type->value,
            'starts_on' => $event->starts_on->toDateString(),
            'ends_on' => $event->ends_on->toDateString(),
        ])->all();
    }

    protected function persist(): Model
    {
        return DB::transaction(function (): AcademicTerm {
            $term = $this->isEditing()
                ? AcademicTerm::findOrFail($this->editingId)
                : new AcademicTerm(['created_by_admin_id' => Auth::guard('admin')->id()]);

            $term->fill([
                'institution_id' => $this->institutionId,
                'name' => trim($this->name),
                'starts_on' => $this->startsOn,
                'ends_on' => $this->endsOn,
            ])->save();

            /** Marcos não são referenciados por nada: substituir é seguro. */
            $term->events()->delete();
            $term->events()->createMany($this->events);

            return $term;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'institutionId' => ['required', 'integer', Rule::exists('institutions', 'id')->whereNull('deleted_at')],
            'name' => [
                'required',
                'string',
                'max:40',
                Rule::unique('academic_terms', 'name')->where('institution_id', $this->institutionId)->ignore($this->editingId),
            ],
            'startsOn' => ['required', 'date'],
            'endsOn' => ['required', 'date', 'after:startsOn'],
            'events' => ['array', 'max:30'],
            'events.*.title' => ['required', 'string', 'max:120'],
            'events.*.type' => ['required', Rule::enum(TermEventType::class)],
            'events.*.starts_on' => ['required', 'date', 'after_or_equal:startsOn', 'before_or_equal:endsOn'],
            'events.*.ends_on' => ['required', 'date', 'after_or_equal:events.*.starts_on', 'before_or_equal:endsOn'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'institutionId' => __('instituição'),
            'name' => __('nome'),
            'startsOn' => __('início'),
            'endsOn' => __('fim'),
            'events.*.title' => __('título do marco'),
            'events.*.starts_on' => __('início do marco'),
            'events.*.ends_on' => __('fim do marco'),
        ];
    }
}
