<?php

namespace App\Http\Requests\Enrollments;

use App\Enums\GradeKind;
use App\Http\Requests\Enrollments\Concerns\ValidatesWeight;
use Illuminate\Validation\Rule;

/**
 * Nota avulsa (sem atividade cadastrada). Notas de atividades são lançadas
 * pelo formulário da própria atividade.
 */
class GradeEntryRequest extends EnrollmentContentRequest
{
    use ValidatesWeight;

    /**
     * Tipos possíveis para nota avulsa ("atividade" vem do formulário da atividade).
     */
    public const STANDALONE_KINDS = [GradeKind::Exam->value, GradeKind::Recovery->value, GradeKind::Manual->value];

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'kind' => ['required', Rule::in(self::STANDALONE_KINDS)],
            'max_points' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'weight' => $this->weightRules(),
            'points' => ['required', 'numeric', 'min:0', 'lte:max_points'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'label' => __('descrição'),
            'kind' => __('tipo'),
            'max_points' => __('valor'),
            'points' => __('nota obtida'),
            'weight' => __('peso'),
        ];
    }
}
