<?php

namespace App\Http\Requests\Enrollments;

use App\Models\AcademicTerm;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Regras dos dados da turma pessoal que o estudante define, compartilhadas
 * entre matricular-se e editar a matrícula.
 */
abstract class ClassGroupSettingsRequest extends FormRequest
{
    /**
     * Matéria cuja instituição restringe os períodos aceitos.
     */
    abstract protected function subject(): ?Subject;

    /**
     * Linhas de horário em branco (o formulário sempre envia uma) são
     * descartadas antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $schedule = collect($this->input('schedule', []))
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['weekday'] ?? null))
            ->map(fn (array $row): array => [...$row, 'weekday' => is_numeric($row['weekday']) ? (int) $row['weekday'] : $row['weekday']])
            ->values()
            ->all();

        $this->merge(['schedule' => $schedule ?: null]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function classGroupRules(): array
    {
        return [
            'academic_term_id' => [
                'nullable',
                'integer',
                Rule::exists('academic_terms', 'id')->whereIn('institution_id', $this->user()->activeInstitutionIds()),
            ],
            'name' => ['nullable', 'string', 'max:80'],
            'room' => ['nullable', 'string', 'max:60'],
            'total_classes' => ['nullable', 'integer', 'min:1', 'max:1000'],

            /** Sem instituição não há de onde herdar as regras: o estudante informa. */
            'total_points' => [Rule::requiredIf(fn () => ! $this->resolvesInstitution()), 'nullable', 'integer', 'min:1', 'max:1000'],
            'passing_percent' => [Rule::requiredIf(fn () => ! $this->resolvesInstitution()), 'nullable', 'numeric', 'min:0', 'max:100'],
            'max_absence_percent' => [Rule::requiredIf(fn () => ! $this->resolvesInstitution()), 'nullable', 'integer', 'min:0', 'max:100'],
            'schedule' => ['nullable', 'array', 'max:14'],
            'schedule.*.weekday' => ['required', 'integer', 'between:0,6'],
            'schedule.*.start' => ['required', 'date_format:H:i'],
            'schedule.*.end' => ['required', 'date_format:H:i', 'after:schedule.*.start'],
        ];
    }

    /**
     * A turma herda regras quando tem período (sempre institucional) ou
     * quando a matéria pertence a uma instituição.
     */
    protected function resolvesInstitution(): bool
    {
        return $this->filled('academic_term_id') || $this->subject()?->institution_id !== null;
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $subject = $this->subject();

                if ($validator->errors()->has('academic_term_id') || ! $this->filled('academic_term_id') || $subject?->institution_id === null) {
                    return;
                }

                $termInstitutionId = AcademicTerm::query()->whereKey($this->integer('academic_term_id'))->value('institution_id');

                if ($termInstitutionId !== $subject->institution_id) {
                    $validator->errors()->add('academic_term_id', __('O período precisa ser da mesma instituição da matéria.'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'academic_term_id' => __('período'),
            'name' => __('turma'),
            'room' => __('sala'),
            'total_classes' => __('total de aulas'),
            'total_points' => __('pontos do período'),
            'passing_percent' => __('percentual para aprovação'),
            'max_absence_percent' => __('limite de faltas'),
            'schedule.*.weekday' => __('dia da semana'),
            'schedule.*.start' => __('início'),
            'schedule.*.end' => __('fim'),
        ];
    }
}
