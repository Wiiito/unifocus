<?php

namespace App\Livewire\Admin\Concerns;

use App\Livewire\Admin\Forms\ResourceForm;
use Illuminate\Database\Eloquent\Model;

/**
 * Comportamento comum das telas de cadastro do painel: modal de
 * criação/edição, exclusão e mensagem de retorno.
 *
 * O componente precisa ter uma propriedade pública $form (ResourceForm).
 *
 * @property ResourceForm $form
 */
trait ManagesResourceForm
{
    public bool $showForm = false;

    public ?string $flashMessage = null;

    abstract protected function findRecord(int $id): Model;

    /**
     * @return array{created: string, updated: string, deleted: string}
     */
    abstract protected function feedbackMessages(): array;

    public function openCreateForm(): void
    {
        $this->closeForm();

        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $this->closeForm();

        $this->form->edit($this->findRecord($id));
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->form->resetForm();
        $this->resetValidation();

        $this->showForm = false;
    }

    public function save(): void
    {
        $message = $this->feedbackMessages()[$this->form->isEditing() ? 'updated' : 'created'];

        $this->form->save();

        $this->closeForm();
        $this->flashMessage = $message;
    }

    public function delete(int $id): void
    {
        $this->findRecord($id)->delete();

        if ($this->form->editingId === $id) {
            $this->closeForm();
        }

        $this->flashMessage = $this->feedbackMessages()['deleted'];
    }
}
