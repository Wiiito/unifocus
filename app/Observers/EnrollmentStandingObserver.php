<?php

namespace App\Observers;

use App\Actions\Enrollments\RecalculateEnrollmentStanding;
use App\Contracts\AffectsEnrollmentStanding;

/**
 * Mantém os caches das matrículas em dia sempre que algo que os afeta muda
 * (nota, presença, aula, atividade, turma ou a própria matrícula).
 */
class EnrollmentStandingObserver
{
    public function __construct(private RecalculateEnrollmentStanding $recalculate) {}

    public function saved(AffectsEnrollmentStanding $model): void
    {
        $this->recalculateFor($model);
    }

    public function deleted(AffectsEnrollmentStanding $model): void
    {
        $this->recalculateFor($model);
    }

    public function restored(AffectsEnrollmentStanding $model): void
    {
        $this->recalculateFor($model);
    }

    private function recalculateFor(AffectsEnrollmentStanding $model): void
    {
        foreach ($model->enrollmentsToRecalculate() as $enrollment) {
            /** Uma matrícula recém-excluída não tem mais o que recalcular. */
            if ($enrollment?->exists) {
                $this->recalculate->handle($enrollment);
            }
        }
    }
}
