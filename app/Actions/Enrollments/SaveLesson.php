<?php

namespace App\Actions\Enrollments;

use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Registra (ou atualiza) uma aula e, no mesmo passo, a presença do estudante
 * nela: enquanto não há integração, é ele quem lança as duas coisas.
 */
class SaveLesson
{
    /**
     * @param  array{title: string, topic?: string|null, description?: string|null, starts_at: string, class_count: int, status: string, attendance_status?: string|null, justification?: string|null}  $data
     */
    public function handle(Enrollment $enrollment, array $data, ?Lesson $lesson = null): Lesson
    {
        return DB::transaction(function () use ($enrollment, $data, $lesson): Lesson {
            $lesson ??= new Lesson([
                'class_group_id' => $enrollment->class_group_id,
                'created_by_user_id' => $enrollment->user_id,
            ]);

            $lesson->fill(Arr::only($data, ['title', 'topic', 'description', 'starts_at', 'class_count', 'status']))->save();

            $this->syncAttendance($enrollment, $lesson, $data['attendance_status'] ?? null, $data['justification'] ?? null);

            return $lesson;
        });
    }

    private function syncAttendance(Enrollment $enrollment, Lesson $lesson, ?string $status, ?string $justification): void
    {
        $attendance = $lesson->attendances()->firstWhere('enrollment_id', $enrollment->id);

        if ($status === null) {
            $attendance?->delete();

            return;
        }

        ($attendance ?? $lesson->attendances()->make(['enrollment_id' => $enrollment->id]))
            ->fill([
                'status' => $status,
                'justification' => $justification,
                'recorded_by_user_id' => $enrollment->user_id,
                'recorded_at' => now(),
            ])
            ->save();
    }
}
