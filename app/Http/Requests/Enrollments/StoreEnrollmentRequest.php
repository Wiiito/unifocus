<?php

namespace App\Http\Requests\Enrollments;

use App\Models\Subject;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEnrollmentRequest extends ClassGroupSettingsRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')
                    ->whereNull('deleted_at')
                    ->where(fn (Builder $query) => $query
                        ->whereNull('institution_id')
                        ->orWhereIn('institution_id', $this->user()->activeInstitutionIds())),
            ],
            ...$this->classGroupRules(),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['subject_id', 'academic_term_id'])) {
                    return;
                }

                /** Repetir a matéria vale; duas vezes no mesmo período, não. */
                $alreadyEnrolled = $this->user()->enrollments()
                    ->whereHas('classGroup', fn ($query) => $query
                        ->where('subject_id', $this->integer('subject_id'))
                        ->where('academic_term_id', $this->input('academic_term_id')))
                    ->exists();

                if ($alreadyEnrolled) {
                    $validator->errors()->add('subject_id', __('Você já está matriculado nesta matéria neste período.'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [...parent::attributes(), 'subject_id' => __('matéria')];
    }

    protected function subject(): ?Subject
    {
        return Subject::find($this->integer('subject_id'));
    }
}
