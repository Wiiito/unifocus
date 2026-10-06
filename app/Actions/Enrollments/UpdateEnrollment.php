<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Atualiza os dados da turma pessoal e a situação da matrícula.
 */
class UpdateEnrollment
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Enrollment $enrollment, array $data): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $data): Enrollment {
            $classGroupAttributes = Arr::only($data, EnrollInSubject::CLASS_GROUP_ATTRIBUTES);
            $classGroupAttributes['name'] = ($data['name'] ?? null) ?: $enrollment->classGroup->name;

            $enrollment->classGroup->update($classGroupAttributes);
            $enrollment->update(Arr::only($data, ['status']));

            return $enrollment;
        });
    }
}
