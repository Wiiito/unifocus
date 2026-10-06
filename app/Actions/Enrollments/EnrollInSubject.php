<?php

namespace App\Actions\Enrollments;

use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Matricula o estudante em uma matéria do catálogo criando a turma pessoal
 * dele: mesmo caminho que turmas compartilhadas vão usar no futuro.
 */
class EnrollInSubject
{
    /**
     * Campos da turma que o próprio estudante define.
     */
    public const CLASS_GROUP_ATTRIBUTES = ['academic_term_id', 'name', 'room', 'schedule', 'total_classes', 'total_points', 'passing_percent', 'max_absence_percent'];

    /**
     * @param  array{subject_id: int, academic_term_id?: int|null, name?: string|null, room?: string|null, schedule?: array<int, array{weekday: int, start: string, end: string}>|null, total_classes?: int|null, total_points?: int|null, passing_percent?: float|null, max_absence_percent?: int|null}  $data
     */
    public function handle(User $user, array $data): Enrollment
    {
        return DB::transaction(function () use ($user, $data): Enrollment {
            $classGroup = ClassGroup::create([
                ...Arr::only($data, self::CLASS_GROUP_ATTRIBUTES),
                'name' => ($data['name'] ?? null) ?: __('Turma pessoal'),
                'subject_id' => $data['subject_id'],
                'created_by_user_id' => $user->id,
                'is_personal' => true,
            ]);

            return $classGroup->enrollments()->create([
                'user_id' => $user->id,
                'enrolled_at' => now(),
            ]);
        });
    }
}
