<?php

namespace App\Livewire\Admin\Forms;

use Illuminate\Database\Eloquent\Model;
use Livewire\Form;

/**
 * Base dos formulários de cadastro do painel. Cada recurso só declara como
 * preencher o formulário a partir do registro (fillFromRecord), as regras
 * (rules) e como gravar (persist); o ciclo criar/editar fica aqui.
 */
abstract class ResourceForm extends Form
{
    public ?int $editingId = null;

    abstract protected function fillFromRecord(Model $record): void;

    abstract protected function persist(): Model;

    public function isEditing(): bool
    {
        return $this->editingId !== null;
    }

    public function edit(Model $record): void
    {
        $this->resetForm();

        $this->editingId = $record->getKey();
        $this->fillFromRecord($record);
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    public function save(): Model
    {
        $this->validate();

        return $this->persist();
    }

    /**
     * Campo de texto opcional: string vazia vira NULL no banco.
     */
    protected function nullable(?string $value): ?string
    {
        return filled($value) ? trim($value) : null;
    }
}
