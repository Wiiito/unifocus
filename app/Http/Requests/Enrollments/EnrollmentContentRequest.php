<?php

namespace App\Http\Requests\Enrollments;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * Base dos formulários de conteúdo da turma (aula, atividade, nota): todos
 * exigem poder editar a matrícula da URL antes de validar qualquer coisa.
 */
abstract class EnrollmentContentRequest extends FormRequest
{
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->route('enrollment'));
    }
}
