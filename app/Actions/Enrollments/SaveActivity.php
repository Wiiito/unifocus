<?php

namespace App\Actions\Enrollments;

use App\Enums\ActivityType;
use App\Enums\GradeKind;
use App\Enums\SubmissionStatus;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Salva uma atividade junto com o que o estudante sabe dela: se já entregou
 * e quanto tirou. Entrega e nota ficam em tabelas próprias; aqui é só o
 * ponto único que mantém as três coerentes.
 */
class SaveActivity
{
    /**
     * @param  array{title: string, description?: string|null, type: string, max_points?: float|null, weight: float, due_at?: string|null, submission_status: string, points?: float|null}  $data
     */
    public function handle(Enrollment $enrollment, array $data, ?Activity $activity = null): Activity
    {
        return DB::transaction(function () use ($enrollment, $data, $activity): Activity {
            $activity ??= new Activity([
                'class_group_id' => $enrollment->class_group_id,
                'created_by_user_id' => $enrollment->user_id,
            ]);

            $activity->fill(Arr::only($data, ['title', 'description', 'type', 'max_points', 'weight', 'due_at']))->save();

            $points = $activity->isGraded() ? ($data['points'] ?? null) : null;

            $this->syncSubmission($enrollment, $activity, SubmissionStatus::from($data['submission_status']), $points !== null);
            $this->syncGrade($enrollment, $activity, $points);

            return $activity;
        });
    }

    private function syncSubmission(Enrollment $enrollment, Activity $activity, SubmissionStatus $status, bool $hasGrade): void
    {
        /** Nota lançada implica atividade corrigida. */
        if ($hasGrade && in_array($status, [SubmissionStatus::Pending, SubmissionStatus::Submitted, SubmissionStatus::Late], true)) {
            $status = SubmissionStatus::Graded;
        }

        $submission = $activity->submissions()->firstOrNew(['enrollment_id' => $enrollment->id]);

        $submission->status = $status;
        $submission->submitted_at = $status->isDelivered() ? ($submission->submitted_at ?? now()) : null;
        $submission->save();
    }

    private function syncGrade(Enrollment $enrollment, Activity $activity, ?float $points): void
    {
        $gradeEntry = $enrollment->gradeEntries()->firstWhere('activity_id', $activity->id);

        if ($points === null) {
            $gradeEntry?->delete();

            return;
        }

        ($gradeEntry ?? $enrollment->gradeEntries()->make(['activity_id' => $activity->id]))
            ->fill([
                'label' => $activity->title,
                'kind' => match ($activity->type) {
                    ActivityType::Exam => GradeKind::Exam,
                    ActivityType::Recovery => GradeKind::Recovery,
                    default => GradeKind::Activity,
                },
                'points' => $points,
                'max_points' => $activity->max_points,
                'weight' => $activity->weight,
                'recorded_by_user_id' => $enrollment->user_id,
                'recorded_at' => now(),
            ])
            ->save();
    }
}
