<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * A matrícula é do estudante. Para qualquer outra pessoa ela "não existe"
 * (404), para não confirmar que o ID é válido.
 */
class EnrollmentPolicy
{
    public function view(User $user, Enrollment $enrollment): Response
    {
        return $this->ownedBy($user, $enrollment);
    }

    /**
     * Editar a matrícula e o conteúdo da turma (aulas, atividades, notas).
     * Só vale para turma pessoal: turmas compartilhadas serão mantidas pela
     * instituição quando a integração existir.
     */
    public function update(User $user, Enrollment $enrollment): Response
    {
        $response = $this->ownedBy($user, $enrollment);

        if ($response->denied()) {
            return $response;
        }

        return $enrollment->classGroup->is_personal
            ? Response::allow()
            : Response::deny(__('Esta turma é mantida pela instituição.'));
    }

    public function delete(User $user, Enrollment $enrollment): Response
    {
        return $this->update($user, $enrollment);
    }

    private function ownedBy(User $user, Enrollment $enrollment): Response
    {
        return $enrollment->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }
}
