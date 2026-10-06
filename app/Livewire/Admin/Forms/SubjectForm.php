<?php

namespace App\Livewire\Admin\Forms;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubjectForm extends ResourceForm
{
    /**
     * Untyped on purpose: selects and number inputs send strings over the
     * wire; validation and the model casts turn them into ints.
     */
    public $institutionId = null;

    public string $name = '';

    public ?string $code = null;

    public ?string $description = null;

    public $credits = null;

    public $workloadHours = null;

    public ?string $color = null;

    protected function fillFromRecord(Model $record): void
    {
        /** @var Subject $record */
        $this->institutionId = $record->institution_id;
        $this->name = $record->name;
        $this->code = $record->code;
        $this->description = $record->description;
        $this->credits = $record->credits;
        $this->workloadHours = $record->workload_hours;
        $this->color = $record->color;
    }

    protected function persist(): Model
    {
        $attributes = [
            'institution_id' => $this->institutionId ?: null,
            'name' => trim($this->name),
            'code' => $this->nullable($this->code),
            'description' => $this->nullable($this->description),
            'credits' => $this->credits ?: null,
            'workload_hours' => $this->workloadHours ?: null,
            'color' => $this->nullable($this->color),
        ];

        if ($this->isEditing()) {
            $subject = Subject::findOrFail($this->editingId);
            $subject->update($attributes);

            return $subject;
        }

        return Subject::create([...$attributes, 'created_by_admin_id' => Auth::guard('admin')->id()]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'institutionId' => ['nullable', 'integer', Rule::exists('institutions', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:160'],

            /** Código único dentro da instituição (ou do catálogo geral). */
            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('subjects', 'code')
                    ->where(fn (Builder $query) => $this->institutionId
                        ? $query->where('institution_id', $this->institutionId)
                        : $query->whereNull('institution_id'))
                    ->ignore($this->editingId),
            ],
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
            'institutionId' => __('instituição'),
            'name' => __('nome'),
            'code' => __('código'),
            'credits' => __('créditos'),
            'workloadHours' => __('carga horária'),
            'color' => __('cor'),
        ];
    }
}
