<?php

namespace App\Livewire\Admin\Forms;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InstitutionForm extends ResourceForm
{
    public string $name = '';

    public ?string $slug = null;

    public ?string $document = null;

    public $totalPoints = Institution::DEFAULT_TOTAL_POINTS;

    public $passingPercent = Institution::DEFAULT_PASSING_PERCENT;

    public $maxAbsencePercent = Institution::DEFAULT_MAX_ABSENCE_PERCENT;

    protected function fillFromRecord(Model $record): void
    {
        /** @var Institution $record */
        $this->name = $record->name;
        $this->slug = $record->slug;
        $this->document = $record->document;
        $this->totalPoints = $record->total_points;
        $this->passingPercent = $record->passing_percent;
        $this->maxAbsencePercent = $record->max_absence_percent;
    }

    /**
     * Sem slug informado, ele é derivado do nome.
     */
    public function save(): Model
    {
        $this->slug = Str::slug($this->nullable($this->slug) ?? $this->name);

        return parent::save();
    }

    protected function persist(): Model
    {
        $attributes = [
            'name' => trim($this->name),
            'slug' => $this->slug,
            'document' => $this->nullable($this->document),
            'total_points' => $this->totalPoints,
            'passing_percent' => $this->passingPercent,
            'max_absence_percent' => $this->maxAbsencePercent,
        ];

        if ($this->isEditing()) {
            $institution = Institution::findOrFail($this->editingId);
            $institution->update($attributes);

            return $institution;
        }

        return Institution::create($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:80', Rule::unique('institutions', 'slug')->ignore($this->editingId)],
            'document' => ['nullable', 'string', 'max:20'],
            'totalPoints' => ['required', 'integer', 'min:1', 'max:1000'],
            'passingPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'maxAbsencePercent' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('nome'),
            'slug' => __('identificador'),
            'document' => __('CNPJ'),
            'totalPoints' => __('pontos do período'),
            'passingPercent' => __('percentual de aprovação'),
            'maxAbsencePercent' => __('limite de faltas'),
        ];
    }
}
